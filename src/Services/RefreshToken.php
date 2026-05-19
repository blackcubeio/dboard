<?php

declare(strict_types=1);

/**
 * RefreshToken.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Oauth2\Interfaces\RefreshTokenInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Yiisoft\ActiveRecord\ActiveRecord;

final class RefreshToken extends ActiveRecord implements RefreshTokenInterface
{
    protected string $token = '';
    protected int $administratorId = 0;
    protected string $clientId = '';
    protected ?string $scopes = null;
    protected DateTimeImmutable|string $expires = '';
    protected bool $revoked = false;
    protected DateTimeImmutable|string $dateCreate = '';

    public function tableName(): string
    {
        return '{{%refreshTokens}}';
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUserId(): string
    {
        return (string) $this->administratorId;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getScopes(): array
    {
        if ($this->scopes === null || $this->scopes === '') {
            return [];
        }
        return explode(' ', $this->scopes);
    }

    public function getExpires(): DateTimeInterface
    {
        if ($this->expires instanceof DateTimeImmutable) {
            return $this->expires;
        }
        return new DateTimeImmutable($this->expires);
    }

    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    public function save(?array $properties = null): void
    {
        if ($this->dateCreate === '') {
            $this->dateCreate = date('Y-m-d H:i:s');
        }
        parent::save($properties);
    }

    public function revoke(): void
    {
        $this->revoked = true;
        $this->save();
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function setAdministratorId(int $administratorId): void
    {
        $this->administratorId = $administratorId;
    }

    public function setUserId(string $userId): void
    {
        $this->administratorId = (int) $userId;
    }

    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function setScopesFromString(?string $scope): void
    {
        $this->scopes = $scope;
    }

    public function setExpires(string $expires): void
    {
        $this->expires = $expires;
    }

    public function setRevoked(bool $revoked): void
    {
        $this->revoked = $revoked;
    }

    public function setDateCreate(string $date): void
    {
        $this->dateCreate = $date;
    }

    public static function queryByToken(string $token): ?static
    {
        return static::query()->where(['token' => $token])->one();
    }
}
