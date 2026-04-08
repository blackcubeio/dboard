<?php

declare(strict_types=1);

/**
 * Create.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Administrators;

use Blackcube\Dboard\Handlers\Commons\AbstractCreate;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\AdministratorForm;
use Blackcube\Dboard\Services\LocaleHelper;

/**
 * Administrator create action.
 */
final class Create extends AbstractCreate
{
    protected function getModelClass(): string { return Administrator::class; }
    protected function getFormModelClass(): string { return AdministratorForm::class; }
    protected function getEntityName(): string { return 'administrator'; }
    protected function getViewPrefix(): string { return 'Administrators'; }
    protected function getListRoute(): string { return 'dboard.administrators'; }
    protected function getSuccessRoute(): string { return 'dboard.administrators.edit'; }

    protected function prepareOutputData(): array
    {
        $output = parent::prepareOutputData();
        if (isset($output['data'])) {
            $output['data']['localeOptions'] = LocaleHelper::getOptions();
        }
        return $output;
    }
}
