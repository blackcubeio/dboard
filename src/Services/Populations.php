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
 * The OAuth2 populations published by the modules (Oauth2Interface), by
 * name. A population is built on first use.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Populations
{
    /**
     * @var array<string, Population>
     */
    private array $populations = [];

    public function __construct(
        private readonly Modules $modules,
        private readonly CypherKey $cypherKey,
        private readonly ScopeProvider $scopeProvider,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * The population, or null when no module publishes it.
     */
    public function get(string $name): ?Population
    {
        $population = null;
        $publishedScopes = $this->modules->getPublishedScopes();
        if (isset($publishedScopes[$name]) === true) {
            if (isset($this->populations[$name]) === false) {
                $this->populations[$name] = new Population(
                    name: $name,
                    scopes: $publishedScopes[$name],
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
        return array_keys($this->modules->getPublishedScopes());
    }
}
