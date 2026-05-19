<?php

declare(strict_types=1);

/**
 * Edit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Contents;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Models\Type;
use Blackcube\Dboard\Handlers\Commons\AbstractEdit;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Forms\ContentForm;

/**
 * Content edit action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Edit extends AbstractEdit
{
    protected function getModelClass(): string { return Content::class; }
    protected function getFormModelClass(): string { return ContentForm::class; }
    protected function getEntityName(): string { return 'content'; }
    protected function getViewPrefix(): string { return 'Contents'; }
    protected function getListRoute(): string { return 'dboard.contents'; }
    protected function getMaxLevel(): int { return 99; }
    protected function getBlocsListId(): string { return 'content-blocs-list'; }
    protected function getBlocReorderRoute(): string { return 'dboard.contents.blocs.reorder'; }
    protected function getBlocAddRoute(): string { return 'dboard.contents.blocs'; }
    protected function getBlocDeleteRoute(): string { return 'dboard.contents.bloc'; }
    protected function stayOnPageAfterSave(): bool { return true; }

    protected function beforeSave(bool $inTransaction): void
    {
        if (!$inTransaction) {
            return;
        }

        $model = $this->models['main'];
        $formModel = $this->formModels['main'];

        // Si languageId a changé et que le content est dans un groupe de traductions
        if ($model->getTranslationGroupId() !== null) {
            $oldValues = $model->oldValues();
            $oldLanguageId = $oldValues['languageId'] ?? null;
            $newLanguageId = $formModel->getLanguageId();

            if ($oldLanguageId !== null && $oldLanguageId !== $newLanguageId) {
                $model->unlinkTranslation();
            }
        }
    }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();

        if ($outputData['type'] === OutputType::Render->value) {
            $outputData['data']['languageQuery'] = Language::query()->active()->orderBy(['name' => SORT_ASC]);
            $outputData['data']['typeQuery'] = Type::query()->orderBy(['name' => SORT_ASC]);
            $outputData['data']['languageCount'] = Language::query()->active()->count();
        }

        return $outputData;
    }
}
