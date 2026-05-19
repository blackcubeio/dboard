<?php

declare(strict_types=1);

/**
 * Edit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Administrators;

use Blackcube\Dboard\Handlers\Commons\AbstractEdit;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\AdministratorForm;
use Blackcube\Dboard\Services\LocaleHelper;

/**
 * Administrator edit action.
 */
final class Edit extends AbstractEdit
{
    protected function getModelClass(): string { return Administrator::class; }
    protected function getFormModelClass(): string { return AdministratorForm::class; }
    protected function getEntityName(): string { return 'administrator'; }
    protected function getViewPrefix(): string { return 'Administrators'; }
    protected function getListRoute(): string { return 'dboard.administrators'; }

    protected function prepareOutputData(): array
    {
        $output = parent::prepareOutputData();
        if (isset($output['data'])) {
            $output['data']['localeOptions'] = LocaleHelper::getOptions();
        }
        return $output;
    }
}
