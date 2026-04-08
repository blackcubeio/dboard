<?php

declare(strict_types=1);

namespace Blackcube\Dboard\Handlers\Xeo;

final class OrganizationDelete extends AbstractGlobalXeoDelete
{
    protected function getKind(): string { return 'Organization'; }
    protected function getKindLabel(): string { return 'organisation'; }
    protected function getIndexRoute(): string { return 'dboard.xeo.organization'; }
}
