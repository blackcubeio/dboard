<?php

declare(strict_types=1);

/**
 * Edit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Tags;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Models\Type;
use Blackcube\Dboard\Handlers\Commons\AbstractEdit;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Forms\TagForm;

/**
 * Tag edit action.
 */
final class Edit extends AbstractEdit
{
    protected function getModelClass(): string { return Tag::class; }
    protected function getFormModelClass(): string { return TagForm::class; }
    protected function getEntityName(): string { return 'tag'; }
    protected function getViewPrefix(): string { return 'Tags'; }
    protected function getListRoute(): string { return 'dboard.tags'; }
    protected function getMaxLevel(): int { return 2; }
    protected function getBlocsListId(): string { return 'tag-blocs-list'; }
    protected function getBlocReorderRoute(): string { return 'dboard.tags.blocs.reorder'; }
    protected function getBlocAddRoute(): string { return 'dboard.tags.blocs'; }
    protected function getBlocDeleteRoute(): string { return 'dboard.tags.bloc'; }
    protected function stayOnPageAfterSave(): bool
    {
        return true;
    }

    protected function beforeSave(bool $inTransaction): void
    {
        if (!$inTransaction) {
            return;
        }

        $model = $this->models['main'];
        $formModel = $this->formModels['main'];

        // If languageId changed and tag is in a translation group
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
