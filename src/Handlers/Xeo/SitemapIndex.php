<?php

declare(strict_types=1);

/**
 * SitemapIndex.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

/**
 * XEO Sitemap index action — lists all hosts with additional Sitemap status.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class SitemapIndex extends AbstractGlobalXeoIndex
{
    protected function getKind(): string { return 'Sitemap'; }
    protected function getEditRoute(): string { return 'dboard.xeo.sitemap.edit'; }
    protected function getToggleRoute(): string { return 'dboard.xeo.sitemap.toggle'; }
    protected function getDeleteRoute(): string { return 'dboard.xeo.sitemap.delete'; }
    protected function getViewName(): string { return 'Xeo/sitemap-index'; }
    protected function getKindLabel(): string { return 'Sitemap additionnel'; }
}
