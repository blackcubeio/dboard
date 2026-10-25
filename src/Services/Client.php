<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Oauth2\Interfaces\ClientInterface;

/**
 * OAuth2 client of the admin panel: public, with the password, refresh token and passkey grants.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Client implements ClientInterface
{
    private ?string $secret = null;
    private bool $public = true;
    private array $redirectUris = [];
    private array $allowedGrants = [
        'password',
        'refresh_token',
        'passkey',
    ];

    public function __construct(
        private string $id,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function getRedirectUris(): array
    {
        return $this->redirectUris;
    }

    public function getAllowedGrants(): array
    {
        return $this->allowedGrants;
    }

    public function validateSecret(string $secret): bool
    {
        return $this->isPublic() === true || ($this->secret !== null && hash_equals($this->secret, $secret) === true);
    }

    public static function queryById(string $clientId): ?static
    {
        return $clientId === 'dboard' ? new static('dboard') : null;
    }
}
