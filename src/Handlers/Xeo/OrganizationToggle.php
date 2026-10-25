<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

/**
 * Enables or disables the global Organization XEO.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class OrganizationToggle extends AbstractGlobalXeoToggle
{
    protected function getKind(): string { return 'Organization'; }
    protected function getKindLabel(): string { return 'Organization'; }
    protected function getIndexRoute(): string { return 'dboard.xeo.organization'; }
}
