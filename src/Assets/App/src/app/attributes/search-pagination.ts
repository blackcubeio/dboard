import {customAttribute, ILogger, INode, resolve} from "aurelia";

@customAttribute('dboard-search-pagination')
export class BlapSearchPaginationCustomAttribute {
    private form?: HTMLFormElement;

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('dboard-search-pagination'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
    ) {
        this.logger.trace('constructor');
    }

    public attaching() {
        this.logger.trace('attaching');
        this.form = this.element.closest('form') as HTMLFormElement;
    }

    public attached() {
        this.logger.trace('attached');
        this.element.addEventListener('click', this.onClick);
    }

    public detached() {
        this.logger.trace('detached');
        this.element.removeEventListener('click', this.onClick);
    }

    private onClick = (event: Event) => {
        const target = event.target as HTMLElement;
        const link = target.closest('a[href]') as HTMLAnchorElement;

        if (!link || !this.form) return;

        const url = new URL(link.href);
        const params = url.searchParams;

        // Find pageXxx param
        for (const [key, value] of params.entries()) {
            if (key.startsWith('page')) {
                this.logger.trace('onClick', key, value);
                const hiddenField = this.form.querySelector(`[data-search-pagination="${key}"]`) as HTMLInputElement;
                if (!hiddenField) {
                    this.logger.warn(`Hidden field for pagination key "${key}" not found in form.`);
                    return;
                }
                event.preventDefault();

                hiddenField.value = value;

                this.form.submit();
                return;
            }
        }
    }
}
