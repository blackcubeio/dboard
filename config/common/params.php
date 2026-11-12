<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Components\Rbac;
use Blackcube\Dboard\Upgrades\RenameMigrations;

return [
    'blackcube/dcore' => [
        // The migrations of the package took the type digit 1: `upgrade` rewrites the history.
        'upgrades' => [
            RenameMigrations::class,
        ],
    ],
    'blackcube/dboard' => [
        'debug' => true,
        'routePrefix' => '/dboard',
        'rbacClasses' => [
            Rbac::class,
        ],
        // The modules of the panel: packages that bring RBAC items, sidebar entries, routes or an
        // OAuth2 population through the interfaces of Interfaces\ without being add-ons, always on.
        // A module declares its class here, in its own params.
        'modules' => [],
        'oauth2' => [
            'issuer' => 'dboard',
            'algorithm' => 'RS256',
            'rsPublicKey' => '', // Path of the key file, must be configured in application
            'rsPrivateKey' => '', // Path of the key file, must be configured in application
            'hsKey' => '', // Shared secret, only for HS* algorithms
            'temporaryPath' => null, // Where keys are written before being moved in place
        ],
        'adminTemplatesAlias' => null, // Yii Alias
        'webauthn' => [
            'rpId' => null,      // null = derived from domain
            'rpName' => 'Blackcube Admin',
            'timeout' => 60000,
            'challengeLength' => 32,
        ],
    ],
];
