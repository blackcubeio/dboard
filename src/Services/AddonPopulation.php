<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\AuthorizationCode;
use Blackcube\Dboard\Models\OauthClient;
use Blackcube\Dboard\Oauth2\JwtAccessToken;
use Blackcube\Dboard\Oauth2\Oauth2Storage;
use Blackcube\Oauth2\Jwt\CypherKey;
use Blackcube\Oauth2\PopulationConfig;
use Blackcube\Oauth2\Server\Oauth2ServerFactory;
use OAuth2\ResponseType\AuthorizationCode as AuthorizationCodeResponseType;
use OAuth2\Server;
use Psr\Log\LoggerInterface;

/**
 * OAuth2 population of an addon resource: the administrators are its users,
 * dynamically registered public clients obtain codes on the consent screen and
 * exchange them with PKCE. Tokens are signed with the dboard key; the
 * population is carried by the issuer and audience claims.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class AddonPopulation
{
    private ?PopulationConfig $config = null;
    private ?Oauth2Storage $storage = null;
    private ?Server $server = null;

    /**
     * @param string[] $scopes The permissions a client can be granted on the resource
     */
    public function __construct(
        public readonly string $name,
        public readonly array $scopes,
        private readonly CypherKey $cypherKey,
        private readonly ScopeProvider $scopeProvider,
        private readonly LoggerInterface $logger,
        public readonly int $accessTokenTtl = 3600,
        public readonly int $refreshTokenTtl = 2592000,
        public readonly int $authorizationCodeTtl = 60,
    ) {
    }

    public function getConfig(): PopulationConfig
    {
        if ($this->config === null) {
            $this->config = new PopulationConfig(
                name: $this->name,
                issuer: $this->name,
                audience: $this->name,
                userQueryClass: Administrator::class,
                clientQueryClass: OauthClient::class,
                refreshTokenQueryClass: RefreshToken::class,
                cypherKeyQueryClass: $this->cypherKey::class,
                algorithm: $this->cypherKey->getAlgorithm(),
                accessTokenTtl: $this->accessTokenTtl,
                refreshTokenTtl: $this->refreshTokenTtl,
                allowedGrants: [
                    'authorization_code',
                    'refresh_token',
                ],
            );
        }
        return $this->config;
    }

    public function getStorage(): Oauth2Storage
    {
        if ($this->storage === null) {
            $config = $this->getConfig();
            $this->storage = new Oauth2Storage(
                userClass: $config->userQueryClass,
                clientClass: $config->clientQueryClass,
                refreshTokenClass: $config->refreshTokenQueryClass,
                scopeProvider: $this->scopeProvider,
                cypherKeyClass: $config->cypherKeyQueryClass,
                authorizationCodeClass: AuthorizationCode::class,
                logger: $this->logger,
            );
        }
        return $this->storage;
    }

    public function getServer(): Server
    {
        if ($this->server === null) {
            $config = $this->getConfig();
            $storage = $this->getStorage();
            $responseTypes = [
                'code' => new AuthorizationCodeResponseType($storage, [
                    'auth_code_lifetime' => $this->authorizationCodeTtl,
                    'enforce_redirect' => true,
                ]),
                'token' => new JwtAccessToken($this->name, $storage, $storage, $storage, [
                    'store_encrypted_token_string' => true,
                    'issuer' => $config->issuer,
                    'access_lifetime' => $config->accessTokenTtl,
                    'refresh_token_lifetime' => $config->refreshTokenTtl,
                ]),
            ];
            $this->server = Oauth2ServerFactory::create($storage, $config, [], $responseTypes);
        }
        return $this->server;
    }
}
