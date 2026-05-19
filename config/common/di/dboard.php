<?php

declare(strict_types=1);

/**
 * dboard.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\DboardConfig;

/** @var array $params */

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
];
