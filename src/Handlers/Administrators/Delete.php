<?php

declare(strict_types=1);

/**
 * Delete.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Administrators;

use Blackcube\Dboard\Handlers\Commons\AbstractDelete;
use Blackcube\Dboard\Models\Administrator;

/**
 * Administrator delete action.
 */
final class Delete extends AbstractDelete
{
    protected function getModelClass(): string { return Administrator::class; }
    protected function getEntityName(): string { return 'administrator'; }
    protected function getListId(): string { return 'administrators-list'; }
    protected function getListRoute(): string { return 'dboard.administrators'; }
}
