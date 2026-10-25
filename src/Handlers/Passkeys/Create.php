<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Passkeys;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Handlers\Commons\AbstractAjaxHandler;
use Blackcube\Dboard\Components\WebauthnHelper;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Services\WebauthnConfig;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Status;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\SessionInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Create passkey (validate and save registration).
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Create extends AbstractAjaxHandler
{
    private ?Administrator $administrator = null;

    public function __construct(
        LoggerInterface $logger,
        DboardConfig $dboardConfig,
        WebViewRenderer $viewRenderer,
        ResponseFactoryInterface $responseFactory,
        JsonResponseFactory $jsonResponseFactory,
        UrlGeneratorInterface $urlGenerator,
        Aliases $aliases,
        TranslatorInterface $translator,
        CurrentRoute $currentRoute,
        protected WebauthnConfig $webauthnConfig,
        protected SessionInterface $session,
    ) {
        parent::__construct(
            logger: $logger,
            dboardConfig: $dboardConfig,
            viewRenderer: $viewRenderer,
            responseFactory: $responseFactory,
            jsonResponseFactory: $jsonResponseFactory,
            urlGenerator: $urlGenerator,
            aliases: $aliases,
            translator: $translator,
            currentRoute: $currentRoute,
        );
    }

    protected function getActionModels(): array
    {
        return [];
    }

    protected function setupAction(): ?ResponseInterface
    {
        $setupResponse = null;
        $this->administrator = $this->request->getAttribute('administrator');

        if ($this->administrator === null) {
            $setupResponse = $this->responseFactory->createResponse(Status::UNAUTHORIZED);
        }

        return $setupResponse;
    }

    protected function handleMethod(): void
    {
        $challengeB64 = $this->session->get('webauthn_challenge');
        $sessionUserId = $this->session->get('webauthn_user_id');

        $this->session->remove('webauthn_challenge');
        $this->session->remove('webauthn_user_id');

        if ($challengeB64 === null || $sessionUserId !== $this->administrator->getId()) {
            throw new \RuntimeException('Invalid session');
        }

        $body = $this->request->getParsedBody();
        if (is_array($body) === false) {
            throw new \RuntimeException('Invalid request');
        }

        $publicKeyCredential = WebauthnHelper::toObject($body, PublicKeyCredential::class);

        if (($publicKeyCredential->response instanceof AuthenticatorAttestationResponse) === false) {
            throw new \RuntimeException('Invalid response type');
        }

        $rpId = $this->webauthnConfig->getRpId($this->request);
        $rpEntity = new PublicKeyCredentialRpEntity(
            $this->webauthnConfig->getRpName(),
            $rpId
        );

        $userEntity = new PublicKeyCredentialUserEntity(
            $this->administrator->getEmail(),
            WebauthnHelper::base64UrlEncode($this->administrator->getId()),
            $this->administrator->getName()
        );

        $creationOptions = PublicKeyCredentialCreationOptions::create(
            $rpEntity,
            $userEntity,
            WebauthnHelper::base64UrlDecode($challengeB64),
            excludeCredentials: [],
        );

        $credentialRecord = WebauthnHelper::getAttestationValidator()->check(
            $publicKeyCredential->response,
            $creationOptions,
            $rpId,
        );

        $userAgent = $this->request->getHeaderLine('User-Agent');
        $deviceName = $this->detectDeviceName($userAgent);

        WebauthnHelper::savePasskey($credentialRecord, $deviceName, (int) $this->administrator->getId());
    }

    protected function prepareOutputData(): array
    {
        return [
            'type' => OutputType::Json->value,
            'data' => [
                'success' => true,
            ],
        ];
    }

    private function detectDeviceName(string $userAgent): string
    {
        if (str_contains($userAgent, 'Windows') === true) {
            $os = 'Windows';
        } elseif (str_contains($userAgent, 'Mac') === true) {
            $os = 'macOS';
        } elseif (str_contains($userAgent, 'Linux') === true) {
            $os = 'Linux';
        } elseif (str_contains($userAgent, 'iPhone') === true || str_contains($userAgent, 'iPad') === true) {
            $os = 'iOS';
        } elseif (str_contains($userAgent, 'Android') === true) {
            $os = 'Android';
        } else {
            $os = 'Unknown';
        }

        if (str_contains($userAgent, 'Chrome') === true) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox') === true) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'Safari') === true) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'Edge') === true) {
            $browser = 'Edge';
        } else {
            $browser = 'Browser';
        }

        return $browser . ' / ' . $os;
    }
}
