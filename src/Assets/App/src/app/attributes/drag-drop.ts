import { bindable, customAttribute, IDisposable, IEventAggregator, ILogger, INode, IPlatform, resolve } from "aurelia";
import { Channels as BleetChannels, IAjaxify, IApiService, IToast, IToaster, ITransitionService, ToasterAction, UiColor } from '@blackcube/aurelia2-bleet';
import { Channels, DragDropAction, DragDropStatus } from '../enums/event-aggregator';
import { IDragDrop, IDragDropStatus } from '../interfaces/event-aggregator';

interface IDragDropResponse {
    ajaxify?: IAjaxify;
    toast?: IToast;
}

@customAttribute({ name: 'dboard-drag-drop', defaultProperty: 'url' })
export class BlapDragDropCustomAttribute {
    @bindable url: string = '';
    @bindable() id: string = '';
    @bindable() dndMode: boolean = false;
    @bindable() csrf: string = '';
    @bindable() errorTitle: string = 'Error';
    @bindable() errorContent: string = 'Reordering failed.';

    private items: HTMLElement[] = [];
    private handles: HTMLElement[] = [];
    private draggedItem: HTMLElement | null = null;
    private placeholder: HTMLElement | null = null;
    private dndEnabled: boolean = false;
    private subscription: IDisposable | null = null;

    private static readonly COLLAPSED_HEIGHT = 70;

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('dboard-drag-drop'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
        private readonly apiService: IApiService = resolve(IApiService),
        private readonly platform: IPlatform = resolve(IPlatform),
        private readonly transitionService: ITransitionService = resolve(ITransitionService),
    ) {
        this.logger.trace('constructor');
    }

    public attached() {
        this.logger.trace('attached', { url: this.url, id: this.id, dndMode: this.dndMode });
        this.collectItems();

        // Subscribe to DragDrop channel
        this.subscription = this.ea.subscribe(Channels.DragDrop, (message: IDragDrop) => {
            this.onDragDropMessage(message);
        });

        // Activate DnD mode if requested
        if (this.dndMode) {
            this.setDndMode(true);
        }
    }

    public detached() {
        this.logger.trace('detached');
        this.cleanupDragEvents();
        this.subscription?.dispose();
    }

    private onDragDropMessage(message: IDragDrop) {
        if (message.id && message.id !== this.id) {
            return;
        }

        this.logger.debug('onDragDropMessage', message);

        switch (message.action) {
            case DragDropAction.Enable:
                this.setDndMode(true);
                break;
            case DragDropAction.Disable:
                this.setDndMode(false);
                break;
            case DragDropAction.Toggle:
                this.setDndMode(!this.dndEnabled);
                break;
        }
    }

    private setDndMode(enabled: boolean) {
        this.dndEnabled = enabled;
        this.logger.debug('setDndMode', { enabled });

        if (enabled) {
            this.enableDndMode();
        } else {
            this.disableDndMode();
        }

        this.ea.publish(Channels.DragDropStatus, <IDragDropStatus>{
            status: enabled ? DragDropStatus.Enabled : DragDropStatus.Disabled,
            id: this.id,
        });
    }

    private enableDndMode() {
        for (const item of this.items) {
            const content = item.querySelector('.p-4') as HTMLElement;

            if (content) {
                // Animation fold (comme dboard-fold)
                content.classList.add('transition-all', 'duration-300', 'overflow-hidden');
                const currentHeight = content.scrollHeight;
                content.style.height = currentHeight + 'px';
                content.offsetHeight; // reflow
                content.style.height = BlapDragDropCustomAttribute.COLLAPSED_HEIGHT + 'px';
            }

            // Icône handle visible
            const handleIcon = item.querySelector('[data-drag-handle]') as HTMLElement;
            if (handleIcon) {
                handleIcon.classList.remove('invisible');
                handleIcon.classList.add('text-primary-600');
            }

            // Layer sur tout le bloc = vrai handle + bloque interactions
            if (!item.querySelector('.dnd-handle-layer')) {
                item.style.position = 'relative';

                const layer = document.createElement('div');
                layer.className = 'dnd-handle-layer absolute inset-0 bg-primary-500/10 z-20 cursor-grab';
                item.appendChild(layer);
            }
        }

        // Les layers sont les vrais handles
        this.handles = Array.from(this.element.querySelectorAll('.dnd-handle-layer')) as HTMLElement[];
        this.initDragEvents();
    }

    private disableDndMode() {
        this.cleanupDragEvents();

        for (const item of this.items) {
            const content = item.querySelector('.p-4') as HTMLElement;

            if (content) {
                // Animation unfold (comme dboard-fold)
                const targetHeight = content.scrollHeight;
                content.style.height = targetHeight + 'px';

                this.transitionService.run(content, (el) => {
                    el.offsetHeight; // reflow
                }, (el) => {
                    this.platform.requestAnimationFrame(() => {
                        el.style.height = '';
                        el.classList.remove('overflow-hidden');
                    });
                });
            }

            // Icône handle invisible
            const handleIcon = item.querySelector('[data-drag-handle]') as HTMLElement;
            if (handleIcon) {
                handleIcon.classList.add('invisible');
                handleIcon.classList.remove('text-primary-600');
            }

            // Supprimer le layer
            item.querySelector('.dnd-handle-layer')?.remove();
            item.style.position = '';
        }
    }

    private collectItems() {
        this.items = Array.from(this.element.querySelectorAll('[data-drag-drop^="item-"]')) as HTMLElement[];
        this.logger.debug('collectItems', { items: this.items.length });
    }

    private initDragEvents() {
        for (const item of this.items) {
            item.addEventListener('dragstart', this.onDragStart);
            item.addEventListener('dragend', this.onDragEnd);
            item.addEventListener('dragover', this.onDragOver);
        }
        for (const handle of this.handles) {
            handle.addEventListener('mousedown', this.onHandleMouseDown);
            handle.addEventListener('mouseup', this.onHandleMouseUp);
        }
        this.element.addEventListener('dragover', this.onContainerDragOver);
        this.element.addEventListener('drop', this.onDrop);
    }

    private cleanupDragEvents() {
        for (const item of this.items) {
            item.removeAttribute('draggable');
            item.removeEventListener('dragstart', this.onDragStart);
            item.removeEventListener('dragend', this.onDragEnd);
            item.removeEventListener('dragover', this.onDragOver);
        }
        for (const handle of this.handles) {
            handle.removeEventListener('mousedown', this.onHandleMouseDown);
            handle.removeEventListener('mouseup', this.onHandleMouseUp);
        }
        this.element.removeEventListener('dragover', this.onContainerDragOver);
        this.element.removeEventListener('drop', this.onDrop);
    }

    private onHandleMouseDown = (event: MouseEvent) => {
        if (!this.dndEnabled) return;

        const handle = event.currentTarget as HTMLElement;
        const item = handle.closest('[data-drag-drop^="item-"]') as HTMLElement;
        if (item) {
            item.setAttribute('draggable', 'true');
            handle.classList.remove('cursor-grab');
            handle.classList.add('cursor-grabbing');
        }
    };

    private onHandleMouseUp = (event: MouseEvent) => {
        const handle = event.currentTarget as HTMLElement;
        const item = handle.closest('[data-drag-drop^="item-"]') as HTMLElement;
        if (item) {
            item.removeAttribute('draggable');
            handle.classList.add('cursor-grab');
            handle.classList.remove('cursor-grabbing');
        }
    };

    private onContainerDragOver = (event: DragEvent) => {
        event.preventDefault();
        event.dataTransfer!.dropEffect = 'move';
    };

    private onDragStart = (event: DragEvent) => {
        const target = event.currentTarget as HTMLElement;
        this.logger.trace('onDragStart', { id: this.getItemId(target) });

        this.draggedItem = target;
        target.classList.add('opacity-50');

        this.placeholder = document.createElement('div');
        this.placeholder.className = 'border-2 border-dashed border-primary-400 rounded-lg bg-primary-50';
        this.placeholder.style.height = target.offsetHeight + 'px';

        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
        }
    };

    private onDragEnd = (event: DragEvent) => {
        const target = event.currentTarget as HTMLElement;
        this.logger.trace('onDragEnd');

        target.classList.remove('opacity-50');
        target.removeAttribute('draggable');
        this.placeholder?.remove();
        this.placeholder = null;
        this.draggedItem = null;
    };

    private onDragOver = (event: DragEvent) => {
        event.preventDefault();
        event.dataTransfer!.dropEffect = 'move';

        const target = event.currentTarget as HTMLElement;
        if (target === this.draggedItem || !this.draggedItem || !this.placeholder) return;

        const rect = target.getBoundingClientRect();
        const midY = rect.top + rect.height / 2;

        if (event.clientY < midY) {
            target.parentNode?.insertBefore(this.placeholder, target);
        } else {
            target.parentNode?.insertBefore(this.placeholder, target.nextSibling);
        }
    };

    private onDrop = (event: DragEvent) => {
        event.preventDefault();
        this.logger.trace('onDrop');

        if (!this.draggedItem || !this.placeholder) return;

        this.placeholder.parentNode?.insertBefore(this.draggedItem, this.placeholder);
        this.placeholder.remove();
        this.placeholder = null;

        this.saveOrder();
    };

    private getItemId(item: HTMLElement): string {
        const attr = item.getAttribute('data-drag-drop') || '';
        return attr.replace('item-', '');
    }

    private saveOrder() {
        const newItems = Array.from(this.element.querySelectorAll('[data-drag-drop^="item-"]')) as HTMLElement[];
        const order = newItems.map(item => this.getItemId(item));

        this.logger.debug('saveOrder', { order });

        if (!this.url) {
            this.logger.warn('No URL configured for drag-drop');
            return;
        }

        const formData = new FormData();
        for (const id of order) {
            formData.append('order[]', id);
        }
        if (this.csrf) {
            formData.append('_csrf', this.csrf);
        }

        this.apiService
            .url(this.url)
            .fromMultipart(formData)
            .request<IDragDropResponse>('POST')
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
