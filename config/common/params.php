<?php

declare(strict_types=1);

/**
 * params.php
 *
 * @copyright 2010-2026 Blackcube
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
        'oauth2' => [
            'issuer' => 'dboard',
            'algorithm' => 'RS256',
            'publicKey' => '', // Must be configured in application
            'privateKey' => '', // Must be configured in application
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
