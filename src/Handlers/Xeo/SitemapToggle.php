<?php

declare(strict_types=1);

namespace Blackcube\Dboard\Handlers\Xeo;

final class SitemapToggle extends AbstractGlobalXeoToggle
{
    protected function getKind(): string { return 'Sitemap'; }
    protected function getKindLabel(): string { return 'sitemap additionnel'; }
    protected function getIndexRoute(): string { return 'dboard.xeo.sitemap'; }
}
