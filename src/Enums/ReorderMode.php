<?php

declare(strict_types=1);

/**
 * ReorderMode.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Enums;

/**
 * Reorder mode for bloc operations.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
enum ReorderMode: string
{
    case MoveUp = 'moveUp';
    case MoveDown = 'moveDown';
    case Dnd = 'dnd';
}
