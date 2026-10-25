<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Oauth2;

use Blackcube\Dboard\Models\AuthorizationCode;
use Blackcube\Oauth2\Interfaces\ScopeProviderInterface;
use Blackcube\Oauth2\Storage\Oauth2Storage as BaseOauth2Storage;
use DateTimeImmutable;
use OAuth2\Storage\AuthorizationCodeInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

/**
 * Storage of an addon population: the oauth2 storage, plus the authorization
 * codes bshaffer needs for the authorization_code grant. A code is consumed on
 * its first exchange.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Oauth2Storage extends BaseOauth2Storage implements AuthorizationCodeInterface
{
    private readonly LoggerInterface $storageLogger;

    /**
     * @param class-string<AuthorizationCode> $authorizationCodeClass
     */
    public function __construct(
        string $userClass,
        string $clientClass,
        string $refreshTokenClass,
        ScopeProviderInterface $scopeProvider,
        string $cypherKeyClass,
        private readonly string $authorizationCodeClass,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct(
            userClass: $userClass,
            clientClass: $clientClass,
            refreshTokenClass: $refreshTokenClass,
            scopeProvider: $scopeProvider,
            cypherKeyClass: $cypherKeyClass,
            logger: $logger,
        );
        $this->storageLogger = $logger ?? new NullLogger();
    }

    public function getAuthorizationCode($code): ?array
    {
        $details = null;
        $authorizationCode = $this->authorizationCodeClass::queryByCode((string) $code);
        if ($authorizationCode !== null) {
            $details = [
                'code' => $authorizationCode->getCode(),
                'client_id' => $authorizationCode->getClientId(),
                'user_id' => $authorizationCode->getUserId(),
                'redirect_uri' => $authorizationCode->getRedirectUri(),
                'expires' => $authorizationCode->getExpires()->getTimestamp(),
                'scope' => implode(' ', $authorizationCode->getScopes()),
                'code_challenge' => $authorizationCode->getCodeChallenge(),
                'code_challenge_method' => $authorizationCode->getCodeChallengeMethod(),
            ];
        }
        return $details;
    }

    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null, $code_challenge = null, $code_challenge_method = null): bool
    {
        $authorizationCode = new $this->authorizationCodeClass();
        $authorizationCode->setCode((string) $code);
        $authorizationCode->setClientId((string) $client_id);
        $authorizationCode->setUserId((string) $user_id);
        $authorizationCode->setRedirectUri((string) $redirect_uri);
        $authorizationCode->setExpires((new DateTimeImmutable())->setTimestamp((int) $expires));
        $authorizationCode->setScopesFromString($scope === null ? null : (string) $scope);
        $authorizationCode->setCodeChallenge($code_challenge === null ? null : (string) $code_challenge);
        $authorizationCode->setCodeChallengeMethod($code_challenge_method === null ? null : (string) $code_challenge_method);

        $saved = false;
        $transaction = $authorizationCode->db()->beginTransaction();
        try {
            $authorizationCode->save();
            $transaction->commit();
            $saved = true;
        } catch (Throwable $e) {
            $transaction->rollBack();
            $this->storageLogger->error('setAuthorizationCode save failed', [
                'exception' => $e->getMessage(),
            ]);
        }
        return $saved;
    }

    public function expireAuthorizationCode($code): bool
    {
        $expired = false;
        $authorizationCode = $this->authorizationCodeClass::queryByCode((string) $code);
        if ($authorizationCode !== null) {
            $transaction = $authorizationCode->db()->beginTransaction();
            try {
                $authorizationCode->delete();
                $transaction->commit();
                $expired = true;
            } catch (Throwable $e) {
                $transaction->rollBack();
                $this->storageLogger->error('expireAuthorizationCode delete failed', [
                    'exception' => $e->getMessage(),
                ]);
            }
        }
        return $expired;
    }
}
