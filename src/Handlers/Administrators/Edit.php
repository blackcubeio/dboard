<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Administrators;

use Blackcube\Dboard\Handlers\Commons\AbstractEdit;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\AdministratorForm;
use Blackcube\Dboard\Helpers\LocaleHelper;

/**
 * Administrator edit action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Edit extends AbstractEdit
{
    protected function getModelClass(): string { return Administrator::class; }
    protected function getFormModelClass(): string { return AdministratorForm::class; }
    protected function getEntityName(): string { return 'administrator'; }
    protected function getViewPrefix(): string { return 'Administrators'; }
    protected function getListRoute(): string { return 'dboard.administrators'; }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();
        if (isset($outputData['data']) === true) {
            $outputData['data']['localeOptions'] = LocaleHelper::getOptions();
        }
        return $outputData;
    }
}
