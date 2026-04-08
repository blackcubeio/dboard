import {bindable, customElement, IEventAggregator, ILogger, INode, IPlatform, resolve} from 'aurelia';
import {IApiService} from '@blackcube/aurelia2-bleet';
import template from './authors.html';

@customElement({ name: 'dboard-authors', template })
export class Authors {
    @bindable() public buildUrl?: string;
    private authorsView: string | null = null;
    private draggedItem: HTMLElement | null = null;
    private placeholder: HTMLElement | null = null;

    public constructor(
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly logger: ILogger = resolve(ILogger).scopeTo('dboard-authors'),
        private readonly apiService: IApiService = resolve(IApiService),
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
        private readonly platform: IPlatform = resolve(IPlatform),
    ) {
        this.logger.trace('Constructor');
    }

    public attached(): void {
        this.logger.trace('Attached');
        this.element.addEventListener('click', this.onClick);
        this.element.addEventListener('mousedown', this.onMouseDown);
        this.element.addEventListener('dragstart', this.onDragStart);
        this.element.addEventListener('dragover', this.onDragOver);
        this.element.addEventListener('dragend', this.onDragEnd);
        this.element.addEventListener('drop', this.onDrop);
    }

    public detaching(): void {
        this.logger.trace('Detaching');
        this.element.removeEventListener('click', this.onClick);
        this.element.removeEventListener('mousedown', this.onMouseDown);
        this.element.removeEventListener('dragstart', this.onDragStart);
        this.element.removeEventListener('dragover', this.onDragOver);
        this.element.removeEventListener('dragend', this.onDragEnd);
        this.element.removeEventListener('drop', this.onDrop);
    }

    // ── Click delegation ──────────────────────────────────────────────

    private onClick = (evt: Event): void => {
        const target = evt.target as HTMLElement;

        const add = target.closest('[data-authors="add"]');
        if (add) {
            evt.preventDefault();
            evt.stopPropagation();
            const select = this.element.querySelector('[data-authors="select"]') as HTMLSelectElement;
            this.add(select);
            return;
        }

        const remove = target.closest('[data-authors="remove"]');
        if (remove) {
            evt.preventDefault();
            evt.stopPropagation();
            const container = remove.closest('[data-authors="author"]') as HTMLElement;
            this.remove(container);
            return;
        }
    };

    private remove(container?: HTMLElement): void {
        this.logger.debug('Removing author');
        if (!container) {
            return;
        }
        container.remove();

        if (!this.buildUrl) {
            return;
        }

        const formData = new FormData();
        this.element.querySelectorAll<HTMLInputElement>('[data-authors="id"]').forEach((input, index) => {
            const name = input.name.replace(/\[\d+\]/, `[${index}]`);
            formData.append(name, input.value);
        });

        this.apiService
            .url(this.buildUrl)
            .fromMultipart(formData)
            .request<any>('POST')
            .then((response) => {
                this.authorsView = response.body;
            })
            .catch((error) => {
                this.logger.error('Remove author failed', error);
            });
    }

    private add(select?: HTMLSelectElement): void {
        this.logger.debug('Adding author');
        if (!this.buildUrl || !select) {
            return;
        }

        const formData = new FormData();
        formData.append(select.name, select.value);
        this.element.querySelectorAll<HTMLInputElement>('[data-authors="id"]').forEach((input) => {
            formData.append(input.name, input.value);
        });

        this.apiService
            .url(this.buildUrl)
            .fromMultipart(formData)
            .request<any>('POST')
            .then((response) => {
                this.authorsView = response.body;
            })
            .catch((error) => {
                this.logger.error('Add author failed', error);
            });
    }

    // ── Drag and drop (DOM only, no persistence) ──────────────────────

    private onMouseDown = (evt: MouseEvent): void => {
        const target = evt.target as HTMLElement;
        const handle = target.closest('[data-authors="handle"]');
        if (!handle) {
            return;
        }

        const item = handle.closest('[data-authors="author"]') as HTMLElement;
        if (item) {
            item.setAttribute('draggable', 'true');
        }
    };

    private onDragStart = (evt: DragEvent): void => {
        const target = evt.target as HTMLElement;
        const item = target.closest('[data-authors="author"]') as HTMLElement;
        if (!item) {
            return;
        }

        this.draggedItem = item;
        item.classList.add('opacity-50');

        this.placeholder = document.createElement('li');
        this.placeholder.className = 'border-2 border-dashed border-primary-400 rounded-lg';
        this.placeholder.style.height = item.offsetHeight + 'px';

        if (evt.dataTransfer) {
            evt.dataTransfer.effectAllowed = 'move';
        }
    };

    private onDragOver = (evt: DragEvent): void => {
        evt.preventDefault();
        if (!this.draggedItem || !this.placeholder) {
            return;
        }

        if (evt.dataTransfer) {
            evt.dataTransfer.dropEffect = 'move';
        }

        const target = evt.target as HTMLElement;
        const item = target.closest('[data-authors="author"]') as HTMLElement;
        if (!item || item === this.draggedItem) {
            return;
        }

        const rect = item.getBoundingClientRect();
        const midY = rect.top + rect.height / 2;

        if (evt.clientY < midY) {
            item.parentNode?.insertBefore(this.placeholder, item);
        } else {
            item.parentNode?.insertBefore(this.placeholder, item.nextSibling);
        }
    };

    private onDrop = (evt: DragEvent): void => {
        evt.preventDefault();
        if (!this.draggedItem || !this.placeholder) {
            return;
        }

        this.placeholder.parentNode?.insertBefore(this.draggedItem, this.placeholder);
        this.cleanup();
    };

    private onDragEnd = (): void => {
        this.cleanup();
    };

    private cleanup(): void {
        if (this.draggedItem) {
            this.draggedItem.classList.remove('opacity-50');
            this.draggedItem.removeAttribute('draggable');
            this.draggedItem = null;
        }
        this.placeholder?.remove();
        this.placeholder = null;
    }
}