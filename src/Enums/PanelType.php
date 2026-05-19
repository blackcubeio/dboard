<?php

declare(strict_types=1);

/**
 * PanelType.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Enums;

/**
 * Panel type enum for modal and drawer distinction.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
enum PanelType: string
{
    case Modal = 'modal';
    case Drawer = 'drawer';
}
