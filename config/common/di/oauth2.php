<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Services\WebauthnConfig;
use Blackcube\Oauth2\Jwt\CypherKey;

/** @var array $params */

return [
    CypherKey::class => [
        'class' => CypherKey::class,
        '__construct()' => [
            'id' => $params['blackcube/dboard']['oauth2']['issuer'],
            'rsPublicKey' => $params['blackcube/dboard']['oauth2']['rsPublicKey'],
            'rsPrivateKey' => $params['blackcube/dboard']['oauth2']['rsPrivateKey'],
            'hsKey' => $params['blackcube/dboard']['oauth2']['hsKey'] ?? '',
            'algorithm' => $params['blackcube/dboard']['oauth2']['algorithm'] ?? 'RS256',
            'temporaryPath' => $params['blackcube/dboard']['oauth2']['temporaryPath'] ?? null,
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
