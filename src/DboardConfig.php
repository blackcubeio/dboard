<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard;

use Blackcube\Handler\HandlerConfig;
use Blackcube\Oauth2\Jwt\CypherKey;
use Blackcube\Dboard\Services\ScopeProvider;
use Blackcube\Dboard\Services\WebauthnConfig;

/**
 * Settings of the admin panel: the handler settings (debug, views alias, layout) plus templates alias, route prefix, RBAC classes, addons, JWT key, scopes and WebAuthn.
 * Its own type keeps the panel handlers apart from the application's HandlerConfig in the same container.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class DboardConfig extends HandlerConfig
{
    public function __construct(
        bool $debug = false,
        string $viewsAlias = '@dboard/Views',
        public readonly ?string $adminTemplatesAlias = null,
        public readonly string $routePrefix = '/admin',
        public readonly array $rbacClasses = [],
        public readonly array $addons = [],
        public readonly ?CypherKey $cypherKey = null,
        public readonly ?ScopeProvider $scopeProvider = null,
        public readonly ?WebauthnConfig $webauthnConfig = null,
    ) {
        parent::__construct(
            handlerNamespacePrefix: 'Blackcube\\Dboard\\Handlers\\',
            viewsAlias: $viewsAlias,
            layoutAlias: $viewsAlias . '/Layouts/main.php',
            debug: $debug,
        );
    }
}
