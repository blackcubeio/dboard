import {customAttribute, ILogger, INode, IPlatform, resolve} from "aurelia";
import {ITransitionService} from '@blackcube/aurelia2-bleet';

@customAttribute('dboard-preview')
export class DboardPreviewCustomAttribute {

    private toggleButtons: HTMLButtonElement[] = [];
    private panel?: HTMLDivElement;
    private dateInput?: HTMLInputElement;
    private applyButton?: HTMLButtonElement;
    private clearButton?: HTMLButtonElement;

    private isActive: boolean = false;
    private isOpen: boolean = false;
    private url: string = '';

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('dboard-preview'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly platform: IPlatform = resolve(IPlatform),
        private readonly transitionService: ITransitionService = resolve(ITransitionService),
    ) {
        this.logger.trace('constructor');
    }

    public attaching() {
        this.logger.trace('attaching');

        this.isActive = this.element.dataset.previewActive === '1';
        this.url = this.element.dataset.previewUrl || '';

        this.toggleButtons = Array.from(this.element.querySelectorAll('[data-preview="toggle"]'));
        this.panel = this.element.querySelector('[data-preview="panel"]') as HTMLDivElement;
        this.dateInput = this.element.querySelector('[data-preview="date"]') as HTMLInputElement;
        this.applyButton = this.element.querySelector('[data-preview="apply"]') as HTMLButtonElement;
        this.clearButton = this.element.querySelector('[data-preview="clear"]') as HTMLButtonElement;
    }

    public attached() {
        this.logger.trace('attached');
        this.toggleButtons.forEach(btn => btn.addEventListener('click', this.onToggleClick));
        this.applyButton?.addEventListener('click', this.onApplyClick);
        this.clearButton?.addEventListener('click', this.onClearClick);
        document.addEventListener('click', this.onDocumentClick);
    }

    public detached() {
        this.logger.trace('detached');
        this.toggleButtons.forEach(btn => btn.removeEventListener('click', this.onToggleClick));
        this.applyButton?.removeEventListener('click', this.onApplyClick);
        this.clearButton?.removeEventListener('click', this.onClearClick);
        document.removeEventListener('click', this.onDocumentClick);
    }

    private onToggleClick = (event: MouseEvent) => {
        event.preventDefault();
        event.stopPropagation();

        if (this.isActive) {
            this.sendToggle(false, null);
        } else {
            if (this.isOpen) {
                this.closePanel();
            } else {
                this.openPanel();
            }
        }
    }

    private onApplyClick = (event: MouseEvent) => {
        event.preventDefault();
        event.stopPropagation();
        const date = this.dateInput?.value || null;
        this.sendToggle(!this.isActive, date);
        this.closePanel();
    }

    private onClearClick = (event: MouseEvent) => {
        event.preventDefault();
        event.stopPropagation();
        if (this.dateInput) {
            this.dateInput.value = '';
        }
    }

    private onDocumentClick = (event: MouseEvent) => {
        if (this.isOpen && !this.element.contains(event.target as Node)) {
            this.closePanel();
        }
    }

    private openPanel() {
        if (!this.panel) return;
        this.isOpen = true;
        this.panel.classList.remove('hidden');
        this.platform.requestAnimationFrame(() => {
            this.panel!.classList.add('opacity-100', 'scale-100');
            this.panel!.classList.remove('opacity-0', 'scale-95');
        });
    }

    private closePanel() {
        if (!this.panel) return;
        this.isOpen = false;
        this.transitionService.run(this.panel, (el) => {
            el.classList.remove('opacity-100', 'scale-100');
            el.classList.add('opacity-0', 'scale-95');
        }, (el) => {
            el.classList.add('hidden');
        });
    }

    private sendToggle(active: boolean, simulateDate: string | null) {
        const formData = new FormData();
        formData.append('active', active ? '1' : '0');
        if (simulateDate) {
            formData.append('simulateDate', simulateDate);
        }

        const csrfMeta = document.querySelector('meta[name="csrf"]');
        const csrfToken = csrfMeta?.getAttribute('content') || '';

        fetch(this.url, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken,
            },
            body: formData,
        })
        .then(response => response.json())
        .then((data: { success: boolean; active: boolean; simulateDate?: string }) => {
            if (data.success) {
                this.isActive = data.active;
                if (this.dateInput && data.simulateDate) {
                    this.dateInput.value = data.simulateDate;
                }
                this.updateIcons();
                this.updateApplyButton();
            }
        })
        .catch(err => {
            this.logger.error('Preview toggle failed', err);
        });
    }

    private updateIcons() {
        // First toggle button = eye (active), second = eye-slash (inactive)
        if (this.toggleButtons.length >= 2) {
            this.toggleButtons[0].classList.toggle('hidden', !this.isActive);
            this.toggleButtons[1].classList.toggle('hidden', this.isActive);
        }
    }

    private updateApplyButton() {
        if (this.applyButton) {
            const activate = this.applyButton.dataset.previewActivate || 'Activate';
            const deactivate = this.applyButton.dataset.previewDeactivate || 'Deactivate';
            this.applyButton.textContent = this.isActive ? deactivate : activate;
        }
    }
}
