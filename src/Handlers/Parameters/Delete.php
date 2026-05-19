<?php

declare(strict_types=1);

/**
 * Delete.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Parameters;

use Blackcube\Dcore\Models\Parameter;
use Blackcube\Dboard\Handlers\Commons\AbstractDelete;

/**
 * Parameter delete action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Delete extends AbstractDelete
{
    protected function getModelClass(): string { return Parameter::class; }
    protected function getEntityName(): string { return 'parameter'; }
    protected function getEntityLabel(): string { return 'parameter'; }
    protected function getListId(): string { return 'parameters-list'; }
    protected function getListRoute(): string { return 'dboard.parameters'; }

    protected function primaryKeys(): array
    {
        return ['domain', 'name'];
    }

    protected function getModelName(): string
    {
        $model = $this->models['main'];
        return $model->getDomain() . '::' . $model->getName();
    }
}
