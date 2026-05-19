<?php

declare(strict_types=1);

/**
 * Edit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Languages;

use Blackcube\Dcore\Models\Language;
use Blackcube\Dboard\Handlers\Commons\AbstractEdit;
use Blackcube\Dboard\Models\Forms\LanguageForm;

/**
 * Language edit action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Edit extends AbstractEdit
{
    protected function getModelClass(): string { return Language::class; }
    protected function getFormModelClass(): string { return LanguageForm::class; }
    protected function getEntityName(): string { return 'language'; }
    protected function getViewPrefix(): string { return 'Languages'; }
    protected function getListRoute(): string { return 'dboard.languages'; }
}
