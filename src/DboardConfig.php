<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard;

use Blackcube\Oauth2\Jwt\CypherKey;
use Blackcube\Dboard\Services\ScopeProvider;
use Blackcube\Dboard\Services\WebauthnConfig;

/**
 * Settings of the admin panel: debug, views and templates aliases, route prefix, RBAC classes, addons, JWT key, scopes and WebAuthn.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class DboardConfig
{
    public function __construct(
        public readonly bool $debug = false,
        public readonly string $viewsAlias = '@dboard/Views',
        public readonly ?string $adminTemplatesAlias = null,
        public readonly string $routePrefix = '/admin',
        public readonly array $rbacClasses = [],
        public readonly array $addons = [],
        public readonly ?CypherKey $cypherKey = null,
        public readonly ?ScopeProvider $scopeProvider = null,
        public readonly ?WebauthnConfig $webauthnConfig = null,
    ) {
    }
}
