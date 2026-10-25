import {bindable, customAttribute, IEventAggregator, ILogger, INode, IPlatform, resolve} from 'aurelia';
import {IApiService} from '@blackcube/aurelia-api';
import {
    Channels,
    ToasterAction,
    UiLevel,
    UiToastIcon
} from '@blackcube/aurelia-kit';
import type {IToast, IToaster} from '@blackcube/aurelia-kit';

@customAttribute('dboard-md-download')
export class BlapMdDownloadCustomAttribute {
    @bindable() public errorTitle: string = 'Error';
    @bindable() public errorContent: string = 'Failed to download markdown.';

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('dboard-md-download'),
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
        private readonly apiService: IApiService = resolve(IApiService),
        private readonly p: IPlatform = resolve(IPlatform),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
    ) {}

    public attached(): void {
        this.element.addEventListener('submit', this.onSubmit);
        this.logger.trace('attached');
    }

    public detaching(): void {
        this.element.removeEventListener('submit', this.onSubmit);
        this.logger.trace('detaching');
    }

    private onSubmit = (evt: SubmitEvent): void => {
        evt.preventDefault();
        evt.stopPropagation();
        this.logger.trace('onSubmit');

        const form = this.element as HTMLFormElement;
        const submitter = evt.submitter as HTMLButtonElement | null;

        if (submitter) {
            submitter.setAttribute('disabled', 'disabled');
        }

        const formData = new FormData(form);

        this.apiService
            .url(form.action)
            .fromMultipart(formData)
            .toBlob()
            .request<Blob>('POST')
            .then((response) => {
                this.logger.debug('Download success');

                let filename = 'export.md';
                const disposition = response.headers['content-disposition'];
                if (disposition) {
                    const match = disposition.match(/filename="?([^";\n]+)"?/);
                    if (match) {
                        filename = match[1];
                    }
                }

                const url = URL.createObjectURL(response.body);
                const a = this.p.document.createElement('a');
                a.href = url;
                a.download = filename;
                a.style.display = 'none';
                this.p.document.body.appendChild(a);
                a.click();
                this.p.document.body.removeChild(a);
                URL.revokeObjectURL(url);
            })
            .catch((error) => {
                this.logger.error('Download failed', error);

                this.ea.publish(Channels.Toaster, <IToaster>{
                    action: ToasterAction.Add,
                    toast: {
                        level: UiLevel.Danger,
                        icon: UiToastIcon.Danger,
                        title: this.errorTitle,
                        content: this.errorContent,
                        duration: 5000,
                    } as IToast,
                });
            })
            .finally(() => {
                if (submitter) {
                    submitter.removeAttribute('disabled');
                }
            });
    };
}
