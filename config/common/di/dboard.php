<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Services\AddonPopulations;
use Blackcube\Dboard\Services\ScopeProvider;

/** @var array $params */

$addons = $params['blackcube/dboard']['addons'] ?? [];
$rbacClasses = $params['blackcube/dboard']['rbacClasses'];
// Each addon may declare its OAuth2 population and the permissions it
// publishes (AddonOauth2Interface); addons sharing a population pool them.
$publishedScopes = [];
foreach ($addons as $addon) {
    if (isset($addon['rbac']) === true) {
        $rbacClasses[] = call_user_func($addon['rbac']);
    }
    if (isset($addon['population'], $addon['scopes']) === true) {
        $population = call_user_func($addon['population']);
        $known = [];
        if (isset($publishedScopes[$population]) === true) {
            $known = $publishedScopes[$population];
        }
        $publishedScopes[$population] = array_values(array_unique(array_merge($known, call_user_func($addon['scopes']))));
    }
}

return [
    DboardConfig::class => [
        'class' => DboardConfig::class,
        '__construct()' => [
            'debug' => $params['blackcube/dboard']['debug'] ?? false,
            'adminTemplatesAlias' => $params['blackcube/dboard']['adminTemplatesAlias'] ?? null,
            'routePrefix' => $params['blackcube/dboard']['routePrefix'],
            'rbacClasses' => $rbacClasses,
            'addons' => $addons,
        ],
    ],
    ScopeProvider::class => [
        'class' => ScopeProvider::class,
        '__construct()' => [
            'publishedScopes' => $publishedScopes,
        ],
    ],
    AddonPopulations::class => [
        'class' => AddonPopulations::class,
        '__construct()' => [
            'publishedScopes' => $publishedScopes,
        ],
    ],
];
