<?php

declare(strict_types=1);

/**
 * Index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Menus;

use Blackcube\Dcore\Models\Menu;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;

/**
 * Menus index action.
 */
final class Index extends AbstractIndex
{
    protected function getModelClass(): string { return Menu::class; }
    protected function getViewPrefix(): string { return 'Menus'; }
}
