<?php

declare(strict_types=1);

/**
 * di.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Services\WebauthnConfig;
use Blackcube\Dboard\Services\CypherKey;

/** @var array $params */

return [
    CypherKey::class => [
        'class' => CypherKey::class,
        '__construct()' => [
            'id' => $params['blackcube/dboard']['oauth2']['issuer'],
            'publicKey' => $params['blackcube/dboard']['oauth2']['publicKey'],
            'privateKey' => $params['blackcube/dboard']['oauth2']['privateKey'],
            'algorithm' => $params['blackcube/dboard']['oauth2']['algorithm'] ?? 'RS256',
        ],
    ],
    WebauthnConfig::class => [
        'class' => WebauthnConfig::class,
        '__construct()' => [
            'rpId' => $params['blackcube/dboard']['webauthn']['rpId'] ?? null,
            'rpName' => $params['blackcube/dboard']['webauthn']['rpName'] ?? 'Blackcube Admin',
            'timeout' => $params['blackcube/dboard']['webauthn']['timeout'] ?? 60000,
            'challengeLength' => $params['blackcube/dboard']['webauthn']['challengeLength'] ?? 32,
        ],
    ],
];
