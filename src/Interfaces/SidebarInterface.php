<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Interfaces;

use Blackcube\Bleet\Widgets\SidebarItem;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * A module that contributes items to the dboard sidebar.
 *
 * getSidebar() receives the rendering context (translator, URL generator, RBAC
 * manager + current user id, current route) and returns the items to merge into
 * the main sidebar. The module does its own permission filtering.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
interface SidebarInterface
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
