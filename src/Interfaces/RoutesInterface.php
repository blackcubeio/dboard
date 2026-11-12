<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Interfaces;

use Yiisoft\Router\Group;

/**
 * A module that contributes admin routes to the dboard router.
 *
 * getRoutesGroup() returns a router Group (e.g. Group::create('/mcp')->routes(...));
 * dboard mounts it inside its own '/dboard' group, as it comes for a module declared in
 * 'blackcube/dboard' => 'modules', behind AddonRoutingMiddleware for an add-on.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
interface RoutesInterface
{
    public static function getRoutesGroup(): Group;
}
