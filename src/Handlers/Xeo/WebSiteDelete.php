<?php

declare(strict_types=1);

namespace Blackcube\Dboard\Handlers\Xeo;

final class WebSiteDelete extends AbstractGlobalXeoDelete
{
    protected function getKind(): string { return 'WebSite'; }
    protected function getKindLabel(): string { return 'site web'; }
    protected function getIndexRoute(): string { return 'dboard.xeo.website'; }
}
