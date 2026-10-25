<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Oauth2\Jwt\CypherKey;
use Psr\Log\LoggerInterface;

/**
 * The OAuth2 populations declared by the addons (AddonOauth2Interface), by
 * name. A population is built on first use.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class AddonPopulations
{
    /**
     * @var array<string, AddonPopulation>
     */
    private array $populations = [];

    /**
     * @param array<string, string[]> $publishedScopes Permissions published by each population
     */
    public function __construct(
        private readonly array $publishedScopes,
        private readonly CypherKey $cypherKey,
        private readonly ScopeProvider $scopeProvider,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function get(string $name): ?AddonPopulation
    {
        $population = null;
        if (isset($this->publishedScopes[$name]) === true) {
            if (isset($this->populations[$name]) === false) {
                $this->populations[$name] = new AddonPopulation(
                    name: $name,
                    scopes: $this->publishedScopes[$name],
                    cypherKey: $this->cypherKey,
                    scopeProvider: $this->scopeProvider,
                    logger: $this->logger,
                );
            }
            $population = $this->populations[$name];
        }
        return $population;
    }

    /**
     * @return string[]
     */
    public function names(): array
    {
        return array_keys($this->publishedScopes);
    }
}
