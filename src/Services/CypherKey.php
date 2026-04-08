<?php

declare(strict_types=1);

/**
 * CypherKey.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Oauth2\Interfaces\CypherKeyInterface;

final class CypherKey implements CypherKeyInterface
{
    private static ?CypherKey $instance = null;

    public function __construct(
        private string $id,
        private string $publicKey,
        private string $privateKey,
        private string $algorithm = 'RS256',
    ) {
        self::$instance = $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    public static function queryById(string $id): ?static
    {
        if (self::$instance !== null && self::$instance->getId() === $id) {
            return self::$instance;
        }
        return null;
    }

    public static function queryDefault(): ?static
    {
        return self::$instance;
    }
}
