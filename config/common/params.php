<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Components\Rbac;

return [
    'blackcube/dboard' => [
        'debug' => true,
        'routePrefix' => '/dboard',
        'rbacClasses' => [
            Rbac::class,
        ],
        'addons' => [],
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
