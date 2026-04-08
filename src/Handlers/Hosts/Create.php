<?php

declare(strict_types=1);

/**
 * Create.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Hosts;

use Blackcube\Dcore\Models\Host;
use Blackcube\Dboard\Handlers\Commons\AbstractCreate;
use Blackcube\Dboard\Models\Forms\HostForm;

/**
 * Host create action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Create extends AbstractCreate
{
    protected function getModelClass(): string { return Host::class; }
    protected function getFormModelClass(): string { return HostForm::class; }
    protected function getEntityName(): string { return 'host'; }
    protected function getViewPrefix(): string { return 'Hosts'; }
    protected function getListRoute(): string { return 'dboard.hosts'; }
    protected function getSuccessRoute(): string { return 'dboard.hosts.edit'; }
}
