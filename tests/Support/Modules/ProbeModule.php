<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Tests\Support\Modules;

use Blackcube\Bleet\Bleet;
use Blackcube\Dboard\Handlers\Dashboard\Index as DashboardIndex;
use Blackcube\Dboard\Interfaces\Oauth2Interface;
use Blackcube\Dboard\Interfaces\RbacInterface;
use Blackcube\Dboard\Interfaces\RoutesInterface;
use Blackcube\Dboard\Interfaces\SidebarInterface;
use Blackcube\Dboard\Middlewares\JwtAuthMiddleware;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * Module of the test suite: not an add-on, no migration, no activation. It brings an RBAC
 * class, a sidebar entry, a route group and an OAuth2 population through the interfaces of
 * the panel alone.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ProbeModule implements RbacInterface, SidebarInterface, RoutesInterface, Oauth2Interface
{
    public const POPULATION = 'probemodule';
    public const ROUTE = 'probe-module.probes';
    public const URL = '/dboard/probe-module/probes';

    public static function getRbac(): string
    {
        return ProbeModuleRbac::class;
    }

    public static function getPopulation(): string
    {
        return self::POPULATION;
    }

    public static function getScopes(): array
    {
        return [
            ProbeModuleRbac::PERMISSION_PROBE_MODULE_VIEW,
        ];
    }

    public static function getSidebar(
        ?TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        ManagerInterface $rbacManager,
        string $userId,
        ?string $currentRoute,
    ): array {
        $items = [];
        if ($rbacManager->userHasPermission($userId, ProbeModuleRbac::PERMISSION_PROBE_MODULE_VIEW) === true) {
            $items[] = Bleet::sidebarItem('Probe module')
                ->outline('beaker')
                ->url($urlGenerator->generate(self::ROUTE));
        }
        return $items;
    }

    public static function getRoutesGroup(): Group
    {
        return Group::create('/probe-module')
            ->routes(
                Route::get('/probes')
                    ->middleware(JwtAuthMiddleware::withRbac([
                        ProbeModuleRbac::PERMISSION_PROBE_MODULE_VIEW,
                    ]))
                    ->action(DashboardIndex::class)
                    ->name(self::ROUTE),
            );
    }
}
