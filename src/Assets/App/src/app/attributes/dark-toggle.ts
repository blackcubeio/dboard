import { customAttribute, INode, IPlatform, resolve } from 'aurelia';
import { IStorageService } from '@blackcube/aurelia-kit';

/**
 * The light / dark switch: a click puts or removes the dark class on <html>
 * and keeps the choice in storage. Without a stored choice, the system
 * preference decides.
 */
@customAttribute('dboard-dark-toggle')
export class DboardDarkToggleCustomAttribute {
    private static readonly STORAGE_KEY = 'dboard_theme';

    public constructor(
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly p: IPlatform = resolve(IPlatform),
        private readonly storage: IStorageService = resolve(IStorageService),
    ) {}

    public attached(): void {
        this.applyTheme();
        this.element.addEventListener('click', this.onClick);
    }

    public detached(): void {
        this.element.removeEventListener('click', this.onClick);
    }

    private onClick = (): void => {
        const root = this.p.document.documentElement;
        const isDark = root.classList.contains('dark');
        if (isDark) {
            root.classList.remove('dark');
            this.storage.save(DboardDarkToggleCustomAttribute.STORAGE_KEY, 'light');
        } else {
            root.classList.add('dark');
            this.storage.save(DboardDarkToggleCustomAttribute.STORAGE_KEY, 'dark');
        }
    };

    private applyTheme(): void {
        const root = this.p.document.documentElement;
        const stored = this.storage.load(DboardDarkToggleCustomAttribute.STORAGE_KEY);
        if (stored === 'dark') {
            root.classList.add('dark');
        } else if (stored === 'light') {
            root.classList.remove('dark');
        } else if (this.p.globalThis.matchMedia('(prefers-color-scheme: dark)').matches) {
            root.classList.add('dark');
        }
    }
}
