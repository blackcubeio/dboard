<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

/**
 * XEO WebSite edit action — edit/create WebSite for a host.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class WebSiteEdit extends AbstractGlobalXeoEdit
{
    protected function getKind(): string { return 'WebSite'; }
    protected function getDefaultName(): string { return 'Site Web'; }
    protected function getViewName(): string { return 'Xeo/website'; }
    protected function getIndexRoute(): string { return 'dboard.xeo.website'; }
}
