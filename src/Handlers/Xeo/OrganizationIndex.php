<?php

declare(strict_types=1);

/**
 * OrganizationIndex.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

/**
 * XEO Organization index action — lists all hosts with Organization status.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class OrganizationIndex extends AbstractGlobalXeoIndex
{
    protected function getKind(): string { return 'Organization'; }
    protected function getEditRoute(): string { return 'dboard.xeo.organization.edit'; }
    protected function getToggleRoute(): string { return 'dboard.xeo.organization.toggle'; }
    protected function getDeleteRoute(): string { return 'dboard.xeo.organization.delete'; }
    protected function getViewName(): string { return 'Xeo/organization-index'; }
    protected function getKindLabel(): string { return 'Organisation'; }
}
