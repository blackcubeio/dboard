<?php

declare(strict_types=1);

/**
 * PasskeyGrant.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Dboard\Components\WebauthnHelper;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Passkey;
use OAuth2\GrantType\GrantTypeInterface;
use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use OAuth2\ResponseType\AccessTokenInterface;
use OAuth2\Storage\UserCredentialsInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSource;

/**
 * OAuth2 Passkey Grant Type.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class PasskeyGrant implements GrantTypeInterface
{
    private ?array $userInfo = null;

    public function __construct(
        private UserCredentialsInterface $storage,
        private WebauthnConfig $webauthnConfig,
        private ?ServerRequestInterface $serverRequest = null,
    ) {}

    public function getQueryStringIdentifier(): string
    {
        return 'passkey';
    }

    public function validateRequest(RequestInterface $request, ResponseInterface $response): bool
    {
        $credentialId = $request->request('credential_id');
        $authenticatorData = $request->request('authenticator_data');
        $clientDataJson = $request->request('client_data_json');
        $signature = $request->request('signature');
        $userHandle = $request->request('user_handle');
        $challenge = $request->request('challenge');

        if (empty($credentialId) || empty($authenticatorData) || empty($clientDataJson) || empty($signature)) {
            $response->setError(400, 'invalid_request', 'Missing passkey parameters');
            return false;
        }

        // Find passkey by credential ID
        $passkey = Passkey::query()
            ->active()
            ->andWhere(['id' => $credentialId])
            ->one();

        if ($passkey === null) {
            $response->setError(401, 'invalid_grant', 'Passkey not found');
            return false;
        }

        // Get administrator
        $administrator = Administrator::query()
            ->active()
            ->andWhere(['id' => $passkey->getAdministratorId()])
            ->one();

        if ($administrator === null) {
            $response->setError(401, 'invalid_grant', 'User not found or inactive');
            return false;
        }

        try {
            // Rebuild credential source from stored data
            $credentialSource = WebauthnHelper::toObject(
                $passkey->getJsonData(),
                PublicKeyCredentialSource::class
            );

            // Rebuild assertion data
            $assertionData = [
                'id' => $credentialId,
                'rawId' => $credentialId,
                'type' => 'public-key',
                'response' => [
                    'authenticatorData' => $authenticatorData,
                    'clientDataJSON' => $clientDataJson,
                    'signature' => $signature,
                    'userHandle' => $userHandle,
                ],
            ];

            $publicKeyCredential = WebauthnHelper::toObject($assertionData, PublicKeyCredential::class);

            if (!$publicKeyCredential->response instanceof \Webauthn\AuthenticatorAssertionResponse) {
                $response->setError(400, 'invalid_request', 'Invalid response type');
                return false;
            }

            // Rebuild request options with challenge
            $requestOptions = PublicKeyCredentialRequestOptions::create(
                WebauthnHelper::base64UrlDecode($challenge),
                userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED,
                rpId: $this->webauthnConfig->getRpId($this->serverRequest),
            );

            // Validate assertion
            $validatedSource = WebauthnHelper::getAssertionValidator()->check(
                $credentialSource,
                $publicKeyCredential->response,
                $requestOptions,
                $this->webauthnConfig->getRpId($this->serverRequest),
                $credentialSource->userHandle
            );

            // Update counter
            $passkey->setCounter($validatedSource->counter);
            $passkey->save();

            $this->userInfo = [
                'user_id' => $administrator->getId(),
                'scope' => null,
            ];

            return true;
        } catch (\Throwable $e) {
            $response->setError(401, 'invalid_grant', 'Passkey validation failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getClientId(): ?string
    {
        return null;
    }

    public function getUserId(): ?string
    {
        return $this->userInfo['user_id'] ?? null;
    }

    public function getScope(): ?string
    {
        return $this->userInfo['scope'] ?? null;
    }

    public function createAccessToken(
        AccessTokenInterface $accessToken,
        $client_id,
        $user_id,
        $scope
    ): array {
        return $accessToken->createAccessToken($client_id, $user_id, $scope);
    }
}
