<?php

declare(strict_types=1);

/**
 * OutputType.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Enums;

/**
 * Enum for action output types.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
enum OutputType: string
{
    case Render = 'render';
    case Partial = 'partial';
    case Json = 'json';
    case Redirect = 'redirect';
}
