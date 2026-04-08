<?php

declare(strict_types=1);

/**
 * Index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\LlmMenus;

use Blackcube\Dcore\Models\LlmMenu;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;

/**
 * LlmMenus index action.
 */
final class Index extends AbstractIndex
{
    protected function getModelClass(): string { return LlmMenu::class; }
    protected function getViewPrefix(): string { return 'LlmMenus'; }
}
