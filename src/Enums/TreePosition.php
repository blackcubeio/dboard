<?php

declare(strict_types=1);

/**
 * TreePosition.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Enums;

/**
 * Enum for tree node positioning in drag-and-drop operations.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
enum TreePosition: string
{
    case Before = 'before';
    case Into = 'into';
    case After = 'after';
}
