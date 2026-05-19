<?php

declare(strict_types=1);

/**
 * OrganizationEdit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

/**
 * XEO Organization edit action — edit/create Organization for a host.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class OrganizationEdit extends AbstractGlobalXeoEdit
{
    protected function getKind(): string { return 'Organization'; }
    protected function getDefaultName(): string { return 'Organisation'; }
    protected function getViewName(): string { return 'Xeo/organization'; }
    protected function getIndexRoute(): string { return 'dboard.xeo.organization'; }
}
