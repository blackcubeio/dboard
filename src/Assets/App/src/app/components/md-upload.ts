import {customElement, IEventAggregator, ILogger, INode, IPlatform, resolve} from 'aurelia';
import {Channels, DrawerAction, DrawerStatus, IApiService, IDrawerStatus} from '@blackcube/aurelia2-bleet';
import template from './md-upload.html';

@customElement({ name: 'dboard-md-upload', template })
export class MdUpload {
    private uploadedView: string | null = null;

    public constructor(
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly logger: ILogger = resolve(ILogger).scopeTo('dboard-md-upload'),
        private readonly apiService: IApiService = resolve(IApiService),
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
        private readonly platform: IPlatform = resolve(IPlatform),
    ) {}

    public attached(): void {
        this.attachFormListener();
    }

    public detaching(): void {
        this.detachFormListener();
    }

    private attachFormListener(): void {
        const form = this.element.querySelector('form');
        if (form) {
            form.addEventListener('submit', this.onSubmit);
        }
    }

    private detachFormListener(): void {
        const form = this.element.querySelector('form');
        if (form) {
            form.removeEventListener('submit', this.onSubmit);
        }
    }

    private onSubmit = (evt: SubmitEvent): void => {
        evt.preventDefault();
        evt.stopPropagation();

        const form = evt.currentTarget as HTMLFormElement;
        form.removeEventListener('submit', this.onSubmit);

        const submitter = evt.submitter as HTMLButtonElement | null;
        if (submitter) {
            submitter.setAttribute('disabled', 'disabled');
        }

        const formData = new FormData(form);

        this.apiService
            .url(form.action)
            .fromMultipart(formData)
            .request<any>('POST')
            .then((response) => {
                if (response.body && typeof response.body === 'object' && response.body.action === 'refreshAndClose') {
                    this.logger.debug('Import success, closing drawer then refreshing');
                    const sub = this.ea.subscribe(Channels.DrawerStatus, (data: IDrawerStatus) => {
                        if (data.status === DrawerStatus.Closed) {
                            sub.dispose();
                            this.platform.window.location.reload();
                        }
                    });
                    this.ea.publish(Channels.Drawer, { id: 'drawer', action: DrawerAction.Close });
                    return;
                }
                this.uploadedView = response.body;
            })
            .catch((error) => {
                this.logger.error('Upload failed', error);
            })
            .finally(() => {
                if (submitter) {
                    submitter.removeAttribute('disabled');
                }
                this.attachFormListener();
            });
    };
}
