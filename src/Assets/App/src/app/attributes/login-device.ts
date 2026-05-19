import {bindable, customAttribute, ILogger, INode, IPlatform, resolve} from 'aurelia';
import {IWebauthnService} from '../services/webauthn-service';

@customAttribute('dboard-login-device')
export class BlapLoginDeviceCustomAttribute {
    @bindable() public challengeUrl: string = '';
    @bindable() public tokenUrl: string = '';

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('dboard-login-device'),
        private readonly webauthnService: IWebauthnService = resolve(IWebauthnService),
        private readonly platform: IPlatform = resolve(IPlatform),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
    ) {}

    public attaching(): void {
        this.logger.trace('attaching');
        this.webauthnService.isAvailable()
            .then((available) => {
                if (!available) {
                    this.logger.debug('WebAuthn not available, removing element');
                    this.element.remove();
                }
            });
    }

    public attached(): void {
        this.element.addEventListener('click', this.onClick);
        this.logger.trace('attached');
    }

    public detaching(): void {
        this.element.removeEventListener('click', this.onClick);
        this.logger.trace('detaching');
    }

    private onClick = (evt: Event): void => {
        evt.preventDefault();
        evt.stopPropagation();
        this.logger.trace('onClick');

        if (!this.challengeUrl || !this.tokenUrl) {
            this.logger.error('Missing challengeUrl or tokenUrl');
            return;
        }

        this.element.setAttribute('disabled', 'disabled');

        this.webauthnService.loginDevice(this.challengeUrl, this.tokenUrl)
            .then((responseBody) => {
                this.logger.debug('Login success');
                if (responseBody.redirect) {
                    this.platform.window.location.href = responseBody.redirect;
                }
            })
            .catch((error) => {
                this.logger.error('Login failed', error);
            })
            .then(() => {
                this.element.removeAttribute('disabled');
            });
    };
}
