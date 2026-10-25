import {bindable, customAttribute, IEventAggregator, ILogger, INode, IPlatform, resolve} from 'aurelia';
import {IWebauthnService} from '../services/webauthn-service';
import {
    Channels,
    ToasterAction,
    UiLevel,
    UiToastIcon
} from '@blackcube/aurelia-kit';
import type {IToast, IToaster} from '@blackcube/aurelia-kit';

@customAttribute('dboard-attach-device')
export class BlapAttachDeviceCustomAttribute {
    @bindable() public challengeUrl: string = '';
    @bindable() public registerUrl: string = '';
    @bindable() public errorTitle: string = 'Error';
    @bindable() public errorContent: string = 'Failed to add passkey.';

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('dboard-attach-device'),
        private readonly webauthnService: IWebauthnService = resolve(IWebauthnService),
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
        private readonly p: IPlatform = resolve(IPlatform),
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

        if (!this.challengeUrl || !this.registerUrl) {
            this.logger.error('Missing challengeUrl or registerUrl');
            return;
        }

        this.element.setAttribute('disabled', 'disabled');

        this.webauthnService.attachDevice(this.challengeUrl, this.registerUrl)
            .then((responseBody) => {
                this.logger.debug('Attach success');

                if (responseBody.toast) {
                    this.ea.publish(Channels.Toaster, <IToaster>{
                        action: ToasterAction.Add,
                        toast: responseBody.toast,
                    });
                }

                if (responseBody.ajaxify) {
                    this.ea.publish(Channels.Ajaxify, responseBody.ajaxify);
                }
            })
            .catch((error) => {
                this.logger.error('Attach failed', error);

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
            .then(() => {
                this.element.removeAttribute('disabled');
            });
    };
}
