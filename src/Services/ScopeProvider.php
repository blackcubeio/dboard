<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Dboard\Models\OauthClient;
use Blackcube\Oauth2\Interfaces\ScopeProviderInterface;

/**
 * Scopes of the OAuth2 populations. The dboard population has none; each addon
 * population publishes the permissions its resource can be granted. A client
 * belongs to one population and asks, by default, everything that population
 * publishes: the consent screen narrows it.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ScopeProvider implements ScopeProviderInterface
{
    public function __construct(
        private readonly Modules $modules,
    ) {
    }

    public function getAvailableScopes(): array
    {
        $scopes = [];
        foreach ($this->modules->getPublishedScopes() as $populationScopes) {
            $scopes = array_merge($scopes, $populationScopes);
        }
        return array_values(array_unique($scopes));
    }

    public function scopeExists(string $scope): bool
    {
        return in_array($scope, $this->getAvailableScopes(), true) === true;
    }

    public function scopesForClient(string $clientId): array
    {
        return $this->scopesOfClientPopulation($clientId);
    }

    public function defaultScopesForClient(string $clientId): array
    {
        return $this->scopesOfClientPopulation($clientId);
    }

    /**
     * @return string[]
     */
    private function scopesOfClientPopulation(string $clientId): array
    {
        $scopes = [];
        $client = OauthClient::queryById($clientId);
        if ($client !== null) {
            $publishedScopes = $this->modules->getPublishedScopes();
            if (isset($publishedScopes[$client->getPopulation()]) === true) {
                $scopes = $publishedScopes[$client->getPopulation()];
            }
        }
        return $scopes;
    }
}
