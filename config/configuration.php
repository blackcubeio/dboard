<?php

declare(strict_types=1);

/**
 * configuration.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

return [
    'config-plugin' => [
        'params' => 'common/params.php',
        'params-web' => 'web/params.php',
        'di' => [
            'common/di/*.php',
        ],
        'bootstrap' => 'common/bootstrap.php',
        'routes' => 'routes.php',
    ],
    'config-plugin-options' => [
        'source-directory' => 'config',
    ],
];
