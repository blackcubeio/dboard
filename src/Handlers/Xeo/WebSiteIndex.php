<?php

declare(strict_types=1);

/**
 * WebSiteIndex.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

/**
 * XEO WebSite index action — lists all hosts with WebSite status.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class WebSiteIndex extends AbstractGlobalXeoIndex
{
    protected function getKind(): string { return 'WebSite'; }
    protected function getEditRoute(): string { return 'dboard.xeo.website.edit'; }
    protected function getToggleRoute(): string { return 'dboard.xeo.website.toggle'; }
    protected function getDeleteRoute(): string { return 'dboard.xeo.website.delete'; }
    protected function getViewName(): string { return 'Xeo/website-index'; }
    protected function getKindLabel(): string { return 'Site Web'; }
}
