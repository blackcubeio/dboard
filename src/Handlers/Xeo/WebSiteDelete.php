<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

/**
 * Deletes the global WebSite XEO.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class WebSiteDelete extends AbstractGlobalXeoDelete
{
    protected function getKind(): string { return 'WebSite'; }
    protected function getKindLabel(): string { return 'Website'; }
    protected function getIndexRoute(): string { return 'dboard.xeo.website'; }
}
