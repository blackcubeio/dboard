<?php

declare(strict_types=1);

/**
 * RobotsIndex.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

/**
 * XEO robots.txt index action — lists all hosts with Robots status.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class RobotsIndex extends AbstractGlobalXeoIndex
{
    protected function getKind(): string { return 'Robots'; }
    protected function getEditRoute(): string { return 'dboard.xeo.robots.edit'; }
    protected function getToggleRoute(): string { return 'dboard.xeo.robots.toggle'; }
    protected function getDeleteRoute(): string { return 'dboard.xeo.robots.delete'; }
    protected function getViewName(): string { return 'Xeo/robots-index'; }
    protected function getKindLabel(): string { return 'robots.txt'; }
}
