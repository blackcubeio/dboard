<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Administrators;

use Blackcube\Dboard\Handlers\Commons\AbstractCreate;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\AdministratorForm;
use Blackcube\Dboard\Helpers\LocaleHelper;

/**
 * Administrator create action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Create extends AbstractCreate
{
    protected function getModelClass(): string { return Administrator::class; }
    protected function getFormModelClass(): string { return AdministratorForm::class; }
    protected function getEntityName(): string { return 'administrator'; }
    protected function getViewPrefix(): string { return 'Administrators'; }
    protected function getListRoute(): string { return 'dboard.administrators'; }
    protected function getSuccessRoute(): string { return 'dboard.administrators.edit'; }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();
        if (isset($outputData['data']) === true) {
            $outputData['data']['localeOptions'] = LocaleHelper::getOptions();
        }
        return $outputData;
    }
}
