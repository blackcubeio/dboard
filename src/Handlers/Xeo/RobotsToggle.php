<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

/**
 * Enables or disables the global robots.txt XEO.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class RobotsToggle extends AbstractGlobalXeoToggle
{
    protected function getKind(): string { return 'Robots'; }
    protected function getKindLabel(): string { return 'robots.txt'; }
    protected function getIndexRoute(): string { return 'dboard.xeo.robots'; }
}
