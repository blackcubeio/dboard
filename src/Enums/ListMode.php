<?php

declare(strict_types=1);

/**
 * ListMode.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Enums;

/**
 * Enum for list display modes.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
enum ListMode: string
{
    case Tree = 'tree';
    case Flat = 'flat';
}
