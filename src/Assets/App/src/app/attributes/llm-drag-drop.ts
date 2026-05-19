import { bindable, customAttribute, IEventAggregator, ILogger, INode, IPlatform, resolve } from "aurelia";
import { Channels as BleetChannels, IAjaxify, IApiService, IToast, IToaster, ToasterAction, UiColor } from '@blackcube/aurelia2-bleet';

interface ILlmDragDropResponse {
    ajaxify?: IAjaxify;
    toast?: IToast;
}

type DropPosition = 'before' | 'into' | 'after';

/**
 * LLM Menu drag-drop.
 *
 * Any item can be dropped before / into / after any other item:
 * - before/after the target -> source becomes a sibling of the target (its level adjusts).
 * - into the target          -> source becomes a child of the target.
 *
 * The "into" zone is hidden when the target is already at MAX_LEVEL (the server's getMaxLevel()),
 * since descending one more level would exceed the tree depth.
 *
 * Every other invariant (no self-drop, no descendant cycle, source's own depth fitting under the
 * target's level, etc.) is enforced by the server (Move handler / AbstractHazeltreeReorder), which
 * rejects with a toast on failure.
 */
@customAttribute({ name: 'dboard-llm-drag-drop', defaultProperty: 'url' })
export class BlapLlmDragDropCustomAttribute {
    @bindable url: string = '';
    @bindable() csrf: string = '';
    @bindable() errorTitle: string = 'Error';
    @bindable() errorContent: string = 'Move failed.';

    private static readonly MAX_LEVEL = 3;

    private items: HTMLElement[] = [];
    private handles: HTMLElement[] = [];
    private draggedItem: HTMLElement | null = null;
    private currentTargetItem: HTMLElement | null = null;
    private dropZonesContainer: HTMLElement | null = null;
    private activeZone: DropPosition | null = null;

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('dboard-llm-drag-drop'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
        private readonly apiService: IApiService = resolve(IApiService),
        private readonly p: IPlatform = resolve(IPlatform),
    ) {
        this.logger.trace('constructor');
    }

    public attached() {
        this.logger.trace('attached', { url: this.url });
        this.collectItems();
        this.initDragEvents();
    }

    public detached() {
        this.logger.trace('detached');
        this.cleanupDragEvents();
        this.removeDropZones();
    }

    private collectItems() {
        this.items = Array.from(this.element.querySelectorAll('[data-llm-drag-drop^="item-"]')) as HTMLElement[];
        this.handles = Array.from(this.element.querySelectorAll('[data-llm-drag-drop="handle"]')) as HTMLElement[];
        this.logger.debug('collectItems', { items: this.items.length, handles: this.handles.length });
    }

    private initDragEvents() {
        for (const item of this.items) {
            item.addEventListener('dragstart', this.onDragStart);
            item.addEventListener('dragend', this.onDragEnd);
            item.addEventListener('dragenter', this.onDragEnter);
            item.addEventListener('dragleave', this.onDragLeave);
            item.addEventListener('dragover', this.onDragOver);
            item.addEventListener('drop', this.onDrop);
        }
        for (const handle of this.handles) {
            handle.addEventListener('mousedown', this.onHandleMouseDown);
            handle.addEventListener('mouseup', this.onHandleMouseUp);
            handle.classList.add('cursor-grab');
        }
    }

    private cleanupDragEvents() {
        for (const item of this.items) {
            item.removeAttribute('draggable');
            item.removeEventListener('dragstart', this.onDragStart);
            item.removeEventListener('dragend', this.onDragEnd);
            item.removeEventListener('dragenter', this.onDragEnter);
            item.removeEventListener('dragleave', this.onDragLeave);
            item.removeEventListener('dragover', this.onDragOver);
            item.removeEventListener('drop', this.onDrop);
        }
        for (const handle of this.handles) {
            handle.removeEventListener('mousedown', this.onHandleMouseDown);
            handle.removeEventListener('mouseup', this.onHandleMouseUp);
            handle.classList.remove('cursor-grab', 'cursor-grabbing');
        }
    }

    private onHandleMouseDown = (event: MouseEvent) => {
        const handle = event.currentTarget as HTMLElement;
        const item = handle.closest('[data-llm-drag-drop^="item-"]') as HTMLElement;
        if (item) {
            item.setAttribute('draggable', 'true');
            handle.classList.remove('cursor-grab');
            handle.classList.add('cursor-grabbing');
        }
    };

    private onHandleMouseUp = (event: MouseEvent) => {
        const handle = event.currentTarget as HTMLElement;
        const item = handle.closest('[data-llm-drag-drop^="item-"]') as HTMLElement;
        if (item) {
            item.removeAttribute('draggable');
            handle.classList.add('cursor-grab');
            handle.classList.remove('cursor-grabbing');
        }
    };

    private onDragStart = (event: DragEvent) => {
        const target = event.currentTarget as HTMLElement;
        this.logger.trace('onDragStart', { id: this.getItemId(target), level: this.getItemLevel(target) });

        this.draggedItem = target;
        target.classList.add('opacity-50');

        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', this.getItemId(target));
        }
    };

    private onDragEnd = (event: DragEvent) => {
        const target = event.currentTarget as HTMLElement;
        this.logger.trace('onDragEnd');

        target.classList.remove('opacity-50');
        target.removeAttribute('draggable');

        const handle = target.querySelector('[data-llm-drag-drop="handle"]') as HTMLElement;
        if (handle) {
            handle.classList.add('cursor-grab');
            handle.classList.remove('cursor-grabbing');
        }

        this.removeDropZones();
        this.draggedItem = null;
        this.currentTargetItem = null;
        this.activeZone = null;
    };

    private onDragEnter = (event: DragEvent) => {
        event.preventDefault();
        const target = event.currentTarget as HTMLElement;

        if (target === this.draggedItem) return;
        if (this.currentTargetItem === target) return;
        if (!this.draggedItem) return;

        this.logger.trace('onDragEnter', { id: this.getItemId(target), level: this.getItemLevel(target) });

        this.removeDropZones();
        this.currentTargetItem = target;
        this.createDropZones(target, this.canDropInto(target));
    };

    private onDragLeave = (event: DragEvent) => {
        const target = event.currentTarget as HTMLElement;
        const relatedTarget = event.relatedTarget as HTMLElement | null;

        if (relatedTarget && target.contains(relatedTarget)) {
            return;
        }

        if (relatedTarget && this.dropZonesContainer?.contains(relatedTarget)) {
            return;
        }

        this.logger.trace('onDragLeave', { id: this.getItemId(target) });

        if (this.currentTargetItem === target) {
            this.removeDropZones();
            this.currentTargetItem = null;
            this.activeZone = null;
        }
    };

    private onDragOver = (event: DragEvent) => {
        event.preventDefault();
        if (event.dataTransfer) {
            event.dataTransfer.dropEffect = 'move';
        }

        const target = event.currentTarget as HTMLElement;
        if (target === this.draggedItem || !this.dropZonesContainer) return;

        const rect = target.getBoundingClientRect();
        const percent = (event.clientY - rect.top) / rect.height;
        const intoAllowed = this.canDropInto(target);

        let newZone: DropPosition;
        if (intoAllowed === true) {
            if (percent < 0.25) {
                newZone = 'before';
            } else if (percent > 0.75) {
                newZone = 'after';
            } else {
                newZone = 'into';
            }
        } else {
            newZone = percent < 0.5 ? 'before' : 'after';
        }

        if (newZone !== this.activeZone) {
            this.activeZone = newZone;
            this.highlightZone(newZone);
        }
    };

    private onDrop = (event: DragEvent) => {
        event.preventDefault();
        event.stopPropagation();

        this.logger.trace('onDrop', { activeZone: this.activeZone });

        if (!this.draggedItem || !this.currentTargetItem || !this.activeZone) {
            return;
        }

        const sourceId = this.getItemId(this.draggedItem);
        const targetId = this.getItemId(this.currentTargetItem);

        if (sourceId === targetId) {
            return;
        }

        this.saveOrder(sourceId, targetId, this.activeZone);
    };

    private canDropInto(target: HTMLElement): boolean {
        return this.getItemLevel(target) < BlapLlmDragDropCustomAttribute.MAX_LEVEL;
    }

    private createDropZones(item: HTMLElement, withInto: boolean) {
        this.dropZonesContainer = this.p.document.createElement('div');
        this.dropZonesContainer.className = 'llm-drop-zones absolute inset-0 pointer-events-none z-10';
        this.dropZonesContainer.style.cssText = 'position: absolute; inset: 0; pointer-events: none; z-index: 10;';

        const beforeZone = this.p.document.createElement('div');
        beforeZone.setAttribute('data-drop-zone', 'before');
        const beforeHeight = withInto === true ? '25%' : '50%';
        beforeZone.style.cssText = `position: absolute; top: 0; left: 0; right: 0; height: ${beforeHeight}; pointer-events: auto;`;

        const beforeLine = this.p.document.createElement('div');
        beforeLine.className = 'llm-drop-line-before';
        beforeLine.style.cssText = 'position: absolute; top: 0; left: 0; right: 0; height: 3px; background-color: transparent; transition: background-color 150ms;';
        beforeZone.appendChild(beforeLine);

        const afterZone = this.p.document.createElement('div');
        afterZone.setAttribute('data-drop-zone', 'after');
        const afterHeight = withInto === true ? '25%' : '50%';
        afterZone.style.cssText = `position: absolute; bottom: 0; left: 0; right: 0; height: ${afterHeight}; pointer-events: auto;`;

        const afterLine = this.p.document.createElement('div');
        afterLine.className = 'llm-drop-line-after';
        afterLine.style.cssText = 'position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background-color: transparent; transition: background-color 150ms;';
        afterZone.appendChild(afterLine);

        this.dropZonesContainer.appendChild(beforeZone);
        this.dropZonesContainer.appendChild(afterZone);

        if (withInto === true) {
            const intoZone = this.p.document.createElement('div');
            intoZone.setAttribute('data-drop-zone', 'into');
            intoZone.style.cssText = 'position: absolute; top: 25%; left: 0; right: 0; height: 50%; pointer-events: auto;';

            const intoHighlight = this.p.document.createElement('div');
            intoHighlight.className = 'llm-drop-highlight-into';
            intoHighlight.style.cssText = 'position: absolute; inset: 0; background-color: transparent; transition: background-color 150ms;';
            intoZone.appendChild(intoHighlight);

            this.dropZonesContainer.appendChild(intoZone);
        }

        const originalPosition = getComputedStyle(item).position;
        if (originalPosition === 'static') {
            item.style.position = 'relative';
        }
        item.appendChild(this.dropZonesContainer);
    }

    private removeDropZones() {
        if (this.dropZonesContainer) {
            const parent = this.dropZonesContainer.parentElement;
            this.dropZonesContainer.remove();
            this.dropZonesContainer = null;

            if (parent && parent.style.position === 'relative') {
                parent.style.position = '';
            }
        }
    }

    private highlightZone(zone: DropPosition) {
        if (!this.dropZonesContainer) return;

        const beforeLine = this.dropZonesContainer.querySelector('.llm-drop-line-before') as HTMLElement;
        const intoHighlight = this.dropZonesContainer.querySelector('.llm-drop-highlight-into') as HTMLElement;
        const afterLine = this.dropZonesContainer.querySelector('.llm-drop-line-after') as HTMLElement;

        if (beforeLine) beforeLine.style.backgroundColor = 'transparent';
        if (intoHighlight) intoHighlight.style.backgroundColor = 'transparent';
        if (afterLine) afterLine.style.backgroundColor = 'transparent';

        switch (zone) {
            case 'before':
                if (beforeLine) beforeLine.style.backgroundColor = 'var(--color-primary-500)';
                break;
            case 'into':
                if (intoHighlight) intoHighlight.style.backgroundColor = 'var(--color-primary-100)';
                break;
            case 'after':
                if (afterLine) afterLine.style.backgroundColor = 'var(--color-primary-500)';
                break;
        }
    }

    private getItemId(item: HTMLElement): string {
        const attr = item.getAttribute('data-llm-drag-drop') || '';
        return attr.replace('item-', '');
    }

    private getItemLevel(item: HTMLElement): number {
        return parseInt(item.getAttribute('data-llm-drag-drop-level') || '0', 10);
    }

    private saveOrder(sourceId: string, targetId: string, position: DropPosition) {
        this.logger.debug('saveOrder', { sourceId, targetId, position });

        if (!this.url) {
            this.logger.warn('No URL configured for llm-drag-drop');
            return;
        }

        const formData = new FormData();
        formData.append('sourceId', sourceId);
        formData.append('targetId', targetId);
        formData.append('position', position);
        if (this.csrf) {
            formData.append('_csrf', this.csrf);
        }

        this.apiService
            .url(this.url)
            .fromMultipart(formData)
            .request<ILlmDragDropResponse>('POST')
            .then((response) => {
                this.logger.debug('saveOrder response', response.body);
                if (response.body.toast) {
                    this.ea.publish(BleetChannels.Toaster, <IToaster>{
                        action: ToasterAction.Add,
                        toast: response.body.toast,
                    });
                }
                if (response.body.ajaxify) {
                    this.ea.publish(BleetChannels.Ajaxify, response.body.ajaxify);
                }
            })
            .catch((error) => {
                this.logger.error('saveOrder failed', error);
                this.ea.publish(BleetChannels.Toaster, <IToaster>{
                    action: ToasterAction.Add,
                    toast: {
                        color: UiColor.Danger,
                        title: this.errorTitle,
                        content: this.errorContent,
                        duration: 5000,
                    } as IToast,
                });
            });
    }
}
