<?php

declare(strict_types=1);

namespace Blackcube\Dboard;

use Blackcube\Dboard\Services\CypherKey;
use Blackcube\Dboard\Services\ScopeProvider;
use Blackcube\Dboard\Services\WebauthnConfig;

final class DboardConfig
{
    public function __construct(
        public readonly bool $debug = false,
        public readonly string $viewsAlias = '@dboard/Views',
        public readonly ?string $adminTemplatesAlias = null,
        public readonly string $routePrefix = '/admin',
        public readonly array $rbacClasses = [],
        public readonly ?CypherKey $cypherKey = null,
        public readonly ?ScopeProvider $scopeProvider = null,
        public readonly ?WebauthnConfig $webauthnConfig = null,
    ) {
    }
}
