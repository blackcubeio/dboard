<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Services\Modules;

/** @var array $params */

// The add-ons are declared to dcore ('blackcube/dcore' => 'addons'), the modules to the panel
// ('blackcube/dboard' => 'modules'); Modules reads their RBAC classes, OAuth2 populations and
// sidebar items at runtime, the add-ons for the active ones only.
return [
    DboardConfig::class => [
        'class' => DboardConfig::class,
        '__construct()' => [
            'debug' => $params['blackcube/dboard']['debug'] ?? false,
            'adminTemplatesAlias' => $params['blackcube/dboard']['adminTemplatesAlias'] ?? null,
            'routePrefix' => $params['blackcube/dboard']['routePrefix'],
            'rbacClasses' => $params['blackcube/dboard']['rbacClasses'],
        ],
    ],
    Modules::class => [
        'class' => Modules::class,
        '__construct()' => [
            'rbacClasses' => $params['blackcube/dboard']['rbacClasses'],
            'modules' => $params['blackcube/dboard']['modules'] ?? [],
        ],
    ],
];
