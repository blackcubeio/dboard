<?php

declare(strict_types=1);

/**
 * Move.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Menus;

use Blackcube\Dcore\Models\Menu;
use Blackcube\Dboard\Handlers\Commons\AbstractHazeltreeReorder;

/**
 * Menu tree move action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Move extends AbstractHazeltreeReorder
{
    protected function getModelClass(): string { return Menu::class; }
    protected function getEntityName(): string { return 'menu'; }
    protected function getListId(): string { return 'menus-list'; }
    protected function getListRoute(): string { return 'dboard.menus'; }
    protected function getMaxLevel(): int { return 4; }
}