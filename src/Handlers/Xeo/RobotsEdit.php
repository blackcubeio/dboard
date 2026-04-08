<?php

declare(strict_types=1);

/**
 * RobotsEdit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

/**
 * XEO robots.txt edit action — edit/create Robots for a host.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class RobotsEdit extends AbstractGlobalXeoEdit
{
    protected function getKind(): string { return 'Robots'; }
    protected function getDefaultName(): string { return 'robots.txt'; }
    protected function getSchemaName(): string { return 'RawData'; }
    protected function getViewName(): string { return 'Xeo/robots'; }
    protected function getIndexRoute(): string { return 'dboard.xeo.robots'; }
}
