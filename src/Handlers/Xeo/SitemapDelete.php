<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

/**
 * Deletes the global additional sitemap XEO.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class SitemapDelete extends AbstractGlobalXeoDelete
{
    protected function getKind(): string { return 'Sitemap'; }
    protected function getKindLabel(): string { return 'Additional sitemap'; }
    protected function getIndexRoute(): string { return 'dboard.xeo.sitemap'; }
}
