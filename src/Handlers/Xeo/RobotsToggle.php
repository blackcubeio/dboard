<?php

declare(strict_types=1);

namespace Blackcube\Dboard\Handlers\Xeo;

final class RobotsToggle extends AbstractGlobalXeoToggle
{
    protected function getKind(): string { return 'Robots'; }
    protected function getKindLabel(): string { return 'robots.txt'; }
    protected function getIndexRoute(): string { return 'dboard.xeo.robots'; }
}
