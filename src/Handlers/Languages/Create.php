<?php

declare(strict_types=1);

/**
 * Create.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Languages;

use Blackcube\Dcore\Models\Language;
use Blackcube\Dboard\Handlers\Commons\AbstractCreate;
use Blackcube\Dboard\Models\Forms\LanguageForm;

/**
 * Language create action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Create extends AbstractCreate
{
    protected function getModelClass(): string { return Language::class; }
    protected function getFormModelClass(): string { return LanguageForm::class; }
    protected function getEntityName(): string { return 'language'; }
    protected function getViewPrefix(): string { return 'Languages'; }
    protected function getListRoute(): string { return 'dboard.languages'; }
    protected function getSuccessRoute(): string { return 'dboard.languages.edit'; }
}
