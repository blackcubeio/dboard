<?php

declare(strict_types=1);

/**
 * Client.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Oauth2\Interfaces\ClientInterface;

final class Client implements ClientInterface
{
    private ?string $secret = null;
    private bool $public = true;
    private array $redirectUris = [];
    private array $allowedGrants = ['password', 'refresh_token', 'passkey'];

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
        if ($this->isPublic()) {
            return true;
        }
        return $this->secret !== null && hash_equals($this->secret, $secret);
    }

    public static function queryById(string $clientId): ?static
    {
        if ($clientId === 'dboard') {
            return new static('dboard');
        }
        return null;
    }
}
