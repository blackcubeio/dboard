<?php

declare(strict_types=1);

/**
 * Widgets.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Widgets;

use Blackcube\Injector\Injector;

/**
 * Widgets factory for Dboard admin panel
 *
 * Usage:
 *   Widgets::sidebar()->user($admin)->currentRoute('dboard.dashboard')->render()
 */
final class Widgets
{
    private static ?Sidebar $sidebar = null;
    private static ?Preview $preview = null;

    public static function sidebar(): Sidebar
    {
        if (self::$sidebar === null) {
            self::$sidebar = Injector::get(Sidebar::class);
        }
        return self::$sidebar;
    }

    public static function preview(): Preview
    {
        if (self::$preview === null) {
            self::$preview = Injector::get(Preview::class);
        }
        return self::$preview;
    }

    public static function popover(string $content = ''): Popover
    {
        return new Popover($content);
    }
}
