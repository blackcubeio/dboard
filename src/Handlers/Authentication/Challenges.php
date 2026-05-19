<?php

declare(strict_types=1);

/**
 * Challenges.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Authentication;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Handlers\Commons\AbstractAjaxHandler;
use Yiisoft\Aliases\Aliases;
use Blackcube\Dboard\Components\WebauthnHelper;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Passkey;
use Blackcube\Dboard\Services\WebauthnConfig;
use Cose\Algorithms;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\SessionInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * WebAuthn challenge generation.
 * - With auth: registration challenge
 * - Without auth: login challenge
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Challenges extends AbstractAjaxHandler
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
        $this->administrator = $this->request->getAttribute('administrator');
        return null;
    }

    protected function prepareOutputData(): array
    {
        // With auth: registration challenge
        if ($this->administrator !== null) {
            return $this->prepareRegistrationChallenge();
        }

        // Without auth: login challenge
        return $this->prepareLoginChallenge();
    }

    private function prepareRegistrationChallenge(): array
    {
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

        $challenge = random_bytes($this->webauthnConfig->getChallengeLength());

        $this->session->set('webauthn_challenge', WebauthnHelper::base64UrlEncode($challenge));
        $this->session->set('webauthn_user_id', $this->administrator->getId());

        $credentialParameters = [
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_ES256K),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_ES256),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_RS256),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_PS256),
            PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_ED256),
        ];

        $authenticatorSelection = AuthenticatorSelectionCriteria::create(
            authenticatorAttachment: AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_PLATFORM,
            userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
            residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_REQUIRED,
        );

        // Exclude existing passkeys
        $query = Passkey::query()
            ->active()
            ->andWhere(['administratorId' => (int) $this->administrator->getId()]);

        $excludeCredentials = [];
        foreach ($query->each() as $passkey) {
            $excludeCredentials[] = PublicKeyCredentialDescriptor::create(
                'public-key',
                WebauthnHelper::base64UrlDecode($passkey->getId())
            );
        }

        $creationOptions = new PublicKeyCredentialCreationOptions(
            rp: $rpEntity,
            user: $userEntity,
            challenge: $challenge,
            pubKeyCredParams: $credentialParameters,
            authenticatorSelection: $authenticatorSelection,
            attestation: null,
            excludeCredentials: $excludeCredentials,
            timeout: $this->webauthnConfig->getTimeout(),
            extensions: null
        );

        $this->session->set('webauthn_creation_options', WebauthnHelper::toArray($creationOptions));

        return [
            'type' => OutputType::Json->value,
            'data' => [
                'mode' => 'registration',
                ...WebauthnHelper::toArray($creationOptions),
            ],
        ];
    }

    private function prepareLoginChallenge(): array
    {
        $challenge = random_bytes($this->webauthnConfig->getChallengeLength());
        $challengeB64 = WebauthnHelper::base64UrlEncode($challenge);

        $this->session->set('webauthn_login_challenge', $challengeB64);

        $rpId = $this->webauthnConfig->getRpId($this->request);

        $requestOptions = PublicKeyCredentialRequestOptions::create(
            $challenge,
            userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED,
            rpId: $rpId,
            timeout: $this->webauthnConfig->getTimeout(),
        );

        $this->session->set('webauthn_request_options', WebauthnHelper::toArray($requestOptions));

        $responseData = WebauthnHelper::toArray($requestOptions);
        $responseData['mode'] = 'login';
        $responseData['challenge_b64'] = $challengeB64;

        return [
            'type' => OutputType::Json->value,
            'data' => $responseData,
        ];
    }
}
