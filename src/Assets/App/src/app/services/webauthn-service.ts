import { DI, ILogger, IPlatform, resolve } from 'aurelia';
import { IApiService } from '@blackcube/aurelia2-bleet';
import { ILoginDeviceResponse, IWebauthnResponse } from '../interfaces';

export const IWebauthnService = DI.createInterface<IWebauthnService>(
    'IWebauthnService',
    (x) => x.singleton(WebauthnService)
);

export interface IWebauthnService extends WebauthnService {}

export class WebauthnService {
    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('WebauthnService'),
        private readonly platform: IPlatform = resolve(IPlatform),
        private readonly apiService: IApiService = resolve(IApiService),
    ) {
        this.logger.debug('Construct');
    }

    public isAvailable(): Promise<boolean> {
        if (!this.platform.window.PublicKeyCredential) {
            this.logger.debug('WebAuthn not supported.');
            return Promise.resolve(false);
        }

        return PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable()
            .then((available) => {
                this.logger.debug(available
                    ? 'WebAuthn supported, Platform Authenticator supported.'
                    : 'WebAuthn supported, Platform Authenticator *not* supported.'
                );
                return available;
            })
            .catch(() => {
                this.logger.debug('WebAuthn check failed.');
                return false;
            });
    }

    public attachDevice(challengeUrl: string, registerUrl: string): Promise<IWebauthnResponse> {
        return this.apiService
            .url(challengeUrl)
            .toJson()
            .post<any>()
            .then((response) => {
                if (response.statusCode !== 200) {
                    throw new Error('Failed to get credential options');
                }

                const options: any = response.body;
                options.challenge = this.base64UrlDecode(options.challenge);
                options.user.id = this.base64UrlDecode(options.user.id);

                if (options.excludeCredentials) {
                    options.excludeCredentials = options.excludeCredentials.map((cred: any) => ({
                        ...cred,
                        id: this.base64UrlDecode(cred.id),
                    }));
                }

                return this.platform.window.navigator.credentials.create({
                    publicKey: options,
                }) as Promise<PublicKeyCredential>;
            })
            .then((credential) => {
                const attestationResponse = credential.response as AuthenticatorAttestationResponse;
                const data = {
                    id: credential.id,
                    rawId: this.base64UrlEncode(credential.rawId),
                    response: {
                        attestationObject: this.base64UrlEncode(attestationResponse.attestationObject),
                        clientDataJSON: this.base64UrlEncode(attestationResponse.clientDataJSON),
                    },
                    type: credential.type,
                };

                return this.apiService
                    .url(registerUrl)
                    .fromJson(data)
                    .toJson()
                    .post<IWebauthnResponse>();
            })
            .then((response) => {
                if (response.statusCode !== 200) {
                    throw new Error('Failed to register credential');
                }
                return response.body;
            })
            .catch((err) => {
                this.logger.error('Attach device failed', err);
                throw err;
            });
    }

    public loginDevice(challengeUrl: string, tokenUrl: string): Promise<ILoginDeviceResponse> {
        let challengeB64: string;

        return this.apiService
            .url(challengeUrl)
            .toJson()
            .post<any>()
            .then((response) => {
                if (response.statusCode !== 200) {
                    throw new Error('Failed to get login challenge');
                }

                const options: any = response.body;
                challengeB64 = options.challenge_b64;
                options.challenge = this.base64UrlDecode(options.challenge);

                if (options.allowCredentials) {
                    options.allowCredentials = options.allowCredentials.map((cred: any) => ({
                        ...cred,
                        id: this.base64UrlDecode(cred.id),
                    }));
                }

                return this.platform.window.navigator.credentials.get({
                    publicKey: options,
                }) as Promise<PublicKeyCredential>;
            })
            .then((credential) => {
                const assertionResponse = credential.response as AuthenticatorAssertionResponse;
                const data = {
                    grant_type: 'passkey',
                    client_id: 'dboard',
                    credential_id: credential.id,
                    authenticator_data: this.base64UrlEncode(assertionResponse.authenticatorData),
                    client_data_json: this.base64UrlEncode(assertionResponse.clientDataJSON),
                    signature: this.base64UrlEncode(assertionResponse.signature),
                    user_handle: assertionResponse.userHandle
                        ? this.base64UrlEncode(assertionResponse.userHandle)
                        : null,
                    challenge: challengeB64,
                };

                return this.apiService
                    .url(tokenUrl)
                    .fromJson(data)
                    .toJson()
                    .post<ILoginDeviceResponse>();
            })
            .then((response) => {
                if (response.statusCode !== 200) {
                    throw new Error('Failed to authenticate with passkey');
                }
                return response.body;
            })
            .catch((err) => {
                this.logger.error('Login device failed', err);
                throw err;
            });
    }

    private base64UrlDecode(input: string): ArrayBuffer {
        const base64 = input.replace(/-/g, '+').replace(/_/g, '/');
        const pad = base64.length % 4;
        const padded = pad ? base64 + '='.repeat(4 - pad) : base64;
        const binary = atob(padded);
        const bytes = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i++) {
            bytes[i] = binary.charCodeAt(i);
        }
        return bytes.buffer;
    }

    private base64UrlEncode(buffer: ArrayBuffer): string {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (let i = 0; i < bytes.length; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        const base64 = btoa(binary);
        return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
    }
}
