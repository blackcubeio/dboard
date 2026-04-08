<?php

declare(strict_types=1);

/**
 * Move.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Contents;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dboard\Handlers\Commons\AbstractHazeltreeReorder;

/**
 * Content tree move action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Move extends AbstractHazeltreeReorder
{
    protected function getModelClass(): string { return Content::class; }
    protected function getEntityName(): string { return 'content'; }
    protected function getListId(): string { return 'contents-list'; }
    protected function getListRoute(): string { return 'dboard.contents'; }
    protected function getMaxLevel(): int { return 99; }
}
