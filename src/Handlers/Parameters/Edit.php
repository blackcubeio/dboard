<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Parameters;

use Blackcube\Dcore\Models\Parameter;
use Blackcube\Dboard\Handlers\Commons\AbstractEdit;
use Blackcube\Dboard\Models\Forms\ParameterForm;

/**
 * Parameter edit action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Edit extends AbstractEdit
{
    protected function getModelClass(): string { return Parameter::class; }
    protected function getFormModelClass(): string { return ParameterForm::class; }
    protected function getEntityName(): string { return 'parameter'; }
    protected function getViewPrefix(): string { return 'Parameters'; }
    protected function getListRoute(): string { return 'dboard.parameters'; }

    protected function primaryKeys(): array
    {
        return [
            'domain',
            'name',
        ];
    }
}
