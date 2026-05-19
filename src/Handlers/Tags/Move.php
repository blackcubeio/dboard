<?php

declare(strict_types=1);

/**
 * Move.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Tags;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Handlers\Commons\AbstractHazeltreeReorder;

/**
 * Tag tree move action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Move extends AbstractHazeltreeReorder
{
    protected function getModelClass(): string { return Tag::class; }
    protected function getEntityName(): string { return 'tag'; }
    protected function getListId(): string { return 'tags-list'; }
    protected function getListRoute(): string { return 'dboard.tags'; }
    protected function getMaxLevel(): int { return 2; }
}
