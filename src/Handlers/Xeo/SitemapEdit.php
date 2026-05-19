<?php

declare(strict_types=1);

/**
 * SitemapEdit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

/**
 * XEO Sitemap edit action — edit/create additional Sitemap data for a host.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class SitemapEdit extends AbstractGlobalXeoEdit
{
    protected function getKind(): string { return 'Sitemap'; }
    protected function getDefaultName(): string { return 'Sitemap additionnel'; }
    protected function getSchemaName(): string { return 'RawData'; }
    protected function getViewName(): string { return 'Xeo/sitemap'; }
    protected function getIndexRoute(): string { return 'dboard.xeo.sitemap'; }
}
