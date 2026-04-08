<?php

declare(strict_types=1);

/**
 * ScopeProvider.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Oauth2\Interfaces\ScopeProviderInterface;

final class ScopeProvider implements ScopeProviderInterface
{
    public function getAvailableScopes(): array
    {
        return ['admin'];
    }

    public function scopeExists(string $scope): bool
    {
        return in_array($scope, $this->getAvailableScopes(), true);
    }

    public function scopesForClient(string $clientId): array
    {
        return $this->getAvailableScopes();
    }

    public function defaultScopesForClient(string $clientId): array
    {
        return ['admin'];
    }
}
