<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Tests\Support\Addons;

use Blackcube\Bleet\Bleet;
use Blackcube\Dboard\Interfaces\Oauth2Interface;
use Blackcube\Dboard\Interfaces\RbacInterface;
use Blackcube\Dboard\Interfaces\SidebarInterface;
use Blackcube\Dcore\Addons\AddonInterface;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * Add-on of the test suite: one revertible migration, an RBAC class, a sidebar entry and an
 * OAuth2 population.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ProbeAddon implements AddonInterface, RbacInterface, SidebarInterface, Oauth2Interface
{
    public const POPULATION = 'probe';
    public const SIDEBAR_URL = '/dboard/probe';

    public static function getName(): string
    {
        return 'Probe';
    }

    public static function getPackage(): string
    {
        return 'blackcube/dboard';
    }

    public static function getMigrationsNamespace(): ?string
    {
        return 'Blackcube\\Dboard\\Tests\\Support\\Addons\\ProbeMigrations';
    }

    public static function getRbac(): string
    {
        return ProbeRbac::class;
    }

    public static function getPopulation(): string
    {
        return self::POPULATION;
    }

    public static function getScopes(): array
    {
        return [
            ProbeRbac::PERMISSION_PROBE_VIEW,
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
        if ($rbacManager->userHasPermission($userId, ProbeRbac::PERMISSION_PROBE_VIEW) === true) {
            $items[] = Bleet::sidebarItem('Probe')
                ->outline('beaker')
                ->url(self::SIDEBAR_URL);
        }
        return $items;
    }
}
