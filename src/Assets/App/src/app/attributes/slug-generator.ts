import { bindable, customAttribute, ILogger, INode, resolve } from "aurelia";
import { IApiService } from '@blackcube/aurelia2-bleet';
import { ISlugGeneratorResponse } from '../interfaces/api-responses';

@customAttribute({ name: 'dboard-slug-generator', defaultProperty: 'url' })
export class BlapSlugGeneratorCustomAttribute {
    @bindable url: string = '';

    private button?: HTMLElement;
    private target?: HTMLInputElement;

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('dboard-slug-generator'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly apiService: IApiService = resolve(IApiService),
    ) {
        this.logger.trace('constructor');
    }

    public attaching() {
        this.logger.trace('attaching');
        this.button = this.element.querySelector('[data-slug-generator="button"]') as HTMLElement;
        this.target = this.element.querySelector('[data-slug-generator="target"]') as HTMLInputElement;

        if (this.button && (!this.target || this.url.trim() === '')) {
            this.logger.warn('Missing target or URL, hiding button');
            this.button.style.display = 'none';
        }
    }

    public attached() {
        if (!this.button || !this.target || this.url.trim() === '') return;

        this.logger.trace('attached', { url: this.url });
        this.button.addEventListener('click', this.onClick);
    }

    public detached() {
        this.logger.trace('detached');
        this.button?.removeEventListener('click', this.onClick);
    }

    private onClick = (event: Event) => {
        event.preventDefault();
        this.logger.trace('onClick', { url: this.url });

        this.apiService
            .url(this.url)
            .request<ISlugGeneratorResponse>('GET')
            .then((response) => {
                this.logger.debug('response', response.body);
                this.target!.value = response.body.url;
                this.target!.dispatchEvent(new Event('input', { bubbles: true }));
            })
            .catch((error) => {
                this.logger.error('failed', error);
            });
    };
}
