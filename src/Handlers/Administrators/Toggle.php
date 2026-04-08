<?php

declare(strict_types=1);

/**
 * Toggle.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Administrators;

use Blackcube\Dboard\Handlers\Commons\AbstractToggle;
use Blackcube\Dboard\Models\Administrator;

/**
 * Administrator toggle action.
 */
final class Toggle extends AbstractToggle
{
    protected function getModelClass(): string { return Administrator::class; }
    protected function getEntityName(): string { return 'administrator'; }
    protected function getListId(): string { return 'administrators-list'; }
    protected function getListRoute(): string { return 'dboard.administrators'; }
}
