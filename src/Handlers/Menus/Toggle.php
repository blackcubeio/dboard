<?php

declare(strict_types=1);

/**
 * Toggle.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Menus;

use Blackcube\Dcore\Models\Menu;
use Blackcube\Dboard\Handlers\Commons\AbstractToggle;

/**
 * Menu toggle action.
 */
final class Toggle extends AbstractToggle
{
    protected function getModelClass(): string { return Menu::class; }
    protected function getEntityName(): string { return 'menu'; }
    protected function getListId(): string { return 'menus-list'; }
    protected function getListRoute(): string { return 'dboard.menus'; }
}
