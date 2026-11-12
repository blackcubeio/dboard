<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Interfaces;

/**
 * An addon that has a configuration screen in the dboard admin.
 *
 * getConfigureRoute() returns the name of its route; the Addons screen links it
 * on the Configure button of the addon while it is active.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
interface AddonConfigureInterface
{
    public static function getConfigureRoute(): string;
}
