<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Addons;

use Yiisoft\Router\Group;

/**
 * An addon that contributes admin routes to the dboard router.
 *
 * getRoutesGroup() returns a router Group (e.g. Group::create('/mcp')->routes(...));
 * dboard mounts it inside its own '/dboard' group.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
interface AddonRoutesInterface
{
    public static function getRoutesGroup(): Group;
}
