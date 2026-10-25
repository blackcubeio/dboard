<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Addons;

use Blackcube\Bleet\Widgets\SidebarItem;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * An addon that contributes items to the dboard sidebar.
 *
 * getSidebar() receives the rendering context (translator, URL generator, RBAC
 * manager + current user id, current route) and returns the items to merge into
 * the main sidebar. The addon does its own permission filtering.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
interface AddonSidebarInterface
{
    /**
     * @return SidebarItem[]
     */
    public static function getSidebar(
        ?TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        ManagerInterface $rbacManager,
        string $userId,
        ?string $currentRoute,
    ): array;
}
