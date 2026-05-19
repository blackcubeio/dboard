import {bindable, customAttribute, ILogger, INode, IPlatform, resolve} from "aurelia";
import {ITransitionService} from '@blackcube/aurelia2-bleet';

@customAttribute('dboard-fold')
export class BlapFoldCustomAttribute {
    @bindable() event: string = 'click';

    private trigger?: HTMLElement;
    private listenerTarget?: HTMLElement;
    private target?: HTMLElement;

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('dboard-fold'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly platform: IPlatform = resolve(IPlatform),
        private readonly transitionService: ITransitionService = resolve(ITransitionService),
    ) {
        this.logger.trace('constructor');
    }

    public attaching() {
        this.logger.trace('attaching');
        this.trigger = this.element.querySelector('[data-fold="trigger"]') as HTMLElement;
        this.target = this.element.querySelector('[data-fold="target"]') as HTMLElement;
        this.target?.classList.add('transition-all', 'duration-300');
    }

    public attached() {
        this.logger.trace('attached');
        if (this.event === 'change') {
            this.listenerTarget = this.trigger?.querySelector('input') as HTMLElement;
        } else {
            this.listenerTarget = this.trigger;
        }
        this.listenerTarget?.addEventListener(this.event, this.onTrigger);
    }

    public detached() {
        this.logger.trace('detached');
        this.listenerTarget?.removeEventListener(this.event, this.onTrigger);
    }

    private onTrigger = (event: Event) => {
        this.logger.trace('onTrigger', event);
        return this.toggle();
    }

    private toggle() {
        if (this.target?.classList.contains('hidden')) {
            this.show();
        } else {
            this.hide();
        }
    }

    private show() {
        if (!this.target) return;
        this.target.classList.remove('hidden');
        this.target.classList.add('overflow-hidden');
        this.target.style.height = '0px';
        this.target.style.opacity = '0';

        this.transitionService.run(this.target, (el) => {
            el.offsetHeight; // reflow
            el.style.height = el.scrollHeight + 'px';
            el.style.opacity = '1';
        }, (el) => {
            this.platform.requestAnimationFrame(() => {
                el.style.height = '';
                el.classList.remove('overflow-hidden');
            });
        });
    }

    private hide() {
        if (!this.target) return;
        this.target.classList.add('overflow-hidden');

        this.transitionService.run(this.target, (el) => {
            const currentHeight = el.scrollHeight;
            el.style.height = currentHeight + 'px';
            el.offsetHeight; // reflow
            el.style.height = '0px';
            el.style.opacity = '0';
        }, (el) => {
            el.classList.add('hidden');
            this.platform.requestAnimationFrame(() => {
                el.style.height = '';
                el.style.opacity = '';
            });
        });
    }
}
