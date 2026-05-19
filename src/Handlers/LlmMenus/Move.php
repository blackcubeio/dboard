<?php

declare(strict_types=1);

/**
 * Move.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\LlmMenus;

use Blackcube\Dcore\Models\LlmMenu;
use Blackcube\Dboard\Handlers\Commons\AbstractHazeltreeReorder;

/**
 * LlmMenu tree move action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Move extends AbstractHazeltreeReorder
{
    protected function getModelClass(): string { return LlmMenu::class; }
    protected function getEntityName(): string { return 'llmMenu'; }
    protected function getListId(): string { return 'llmmenus-list'; }
    protected function getListRoute(): string { return 'dboard.llmmenus'; }
    protected function getMaxLevel(): int { return 3; }
}
