<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

/**
 * Deletes the global Organization XEO.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class OrganizationDelete extends AbstractGlobalXeoDelete
{
    protected function getKind(): string { return 'Organization'; }
    protected function getKindLabel(): string { return 'Organization'; }
    protected function getIndexRoute(): string { return 'dboard.xeo.organization'; }
}
