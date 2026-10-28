import {bindable, customElement, ILogger, INode, IPlatform, resolve} from 'aurelia';
import {IApiService} from '@blackcube/aurelia-api';
import {ITransitionService} from '@blackcube/aurelia-kit';

interface IUnfoldItem {
    label: string;
    url: string;
    detail: string | null;
}

interface IUnfoldResponse {
    items: IUnfoldItem[];
    total: number;
}

/**
 * A row that unfolds the records behind it: the slot carries the trigger
 * rendered by the server (data-unfold="trigger"), the first click reads the
 * list once from the url, as {items: [{label, url, detail}], total}, and the
 * clicks after only fold and unfold it.
 */
@customElement('dboard-unfold')
export class Unfold {
    @bindable() public url: string = '';
    @bindable() public moreLabel: string = '';
    public items: IUnfoldItem[] = [];
    public more: number = 0;
    public loaded: boolean = false;

    private trigger?: HTMLElement;
    private target?: HTMLElement;

    public constructor(
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly logger: ILogger = resolve(ILogger).scopeTo('dboard-unfold'),
        private readonly apiService: IApiService = resolve(IApiService),
        private readonly transitionService: ITransitionService = resolve(ITransitionService),
        private readonly p: IPlatform = resolve(IPlatform),
    ) {
        this.logger.trace('constructor');
    }

    public get moreText(): string {
        return this.moreLabel.replace('{count}', String(this.more));
    }

    public attached(): void {
        this.logger.trace('attached', {url: this.url});
        this.trigger = this.element.querySelector('[data-unfold="trigger"]') as HTMLElement;
        this.target = this.element.querySelector('[data-unfold="target"]') as HTMLElement;
        this.target?.classList.add('transition-all', 'duration-300');
        this.trigger?.addEventListener('click', this.onTrigger);
    }

    public detaching(): void {
        this.logger.trace('detaching');
        this.trigger?.removeEventListener('click', this.onTrigger);
    }

    private onTrigger = (event: Event): void => {
        event.preventDefault();
        if (this.loaded === false) {
            this.load();
        } else {
            this.toggle();
        }
    };

    private load(): void {
        this.logger.debug('load', {url: this.url});
        this.apiService
            .url(this.url)
            .request<IUnfoldResponse>('GET')
            .then((response) => {
                this.items = response.body.items;
                this.more = response.body.total - response.body.items.length;
                this.loaded = true;
                this.p.requestAnimationFrame(() => {
                    this.show();
                });
            })
            .catch((error) => {
                this.logger.error('load failed', error);
            });
    }

    private toggle(): void {
        if (this.target?.classList.contains('hidden')) {
            this.show();
        } else {
            this.hide();
        }
    }

    private show(): void {
        if (!this.target) {
            return;
        }
        this.trigger?.setAttribute('aria-expanded', 'true');
        this.target.classList.remove('hidden');
        this.target.classList.add('overflow-hidden');
        this.target.style.height = '0px';
        this.target.style.opacity = '0';

        this.transitionService.run(this.target, (el) => {
            el.offsetHeight; // reflow
            el.style.height = el.scrollHeight + 'px';
            el.style.opacity = '1';
        }, (el) => {
            this.p.requestAnimationFrame(() => {
                el.style.height = '';
                el.classList.remove('overflow-hidden');
            });
        });
    }

    private hide(): void {
        if (!this.target) {
            return;
        }
        this.trigger?.setAttribute('aria-expanded', 'false');
        this.target.classList.add('overflow-hidden');

        this.transitionService.run(this.target, (el) => {
            const currentHeight = el.scrollHeight;
            el.style.height = currentHeight + 'px';
            el.offsetHeight; // reflow
            el.style.height = '0px';
            el.style.opacity = '0';
        }, (el) => {
            el.classList.add('hidden');
            this.p.requestAnimationFrame(() => {
                el.style.height = '';
                el.style.opacity = '';
            });
        });
    }
}
