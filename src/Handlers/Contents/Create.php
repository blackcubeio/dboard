<?php

declare(strict_types=1);

/**
 * Create.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Contents;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Models\Type;
use Blackcube\Dboard\Handlers\Commons\AbstractCreate;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Enums\TreePosition;
use Blackcube\Dboard\Models\Forms\ContentForm;

/**
 * Content create action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Create extends AbstractCreate
{
    protected function getModelClass(): string { return Content::class; }
    protected function getFormModelClass(): string { return ContentForm::class; }
    protected function getEntityName(): string { return 'content'; }
    protected function getViewPrefix(): string { return 'Contents'; }
    protected function getListRoute(): string { return 'dboard.contents'; }
    protected function getSuccessRoute(): string { return 'dboard.contents.edit'; }
    protected function getMaxLevel(): int { return 99; }

    protected function getDefaultMoveTarget(): ?array
    {
        $last = Content::query()->orderBy(['dateCreate' => SORT_DESC])->one();
        if ($last === null) {
            return null;
        }
        return ['targetId' => $last->getId(), 'mode' => TreePosition::After->value];
    }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();
        if ($outputData['type'] === OutputType::Render->value) {
            $outputData['data']['languageQuery'] = Language::query()->active()->orderBy(['name' => SORT_ASC]);
            $outputData['data']['typeQuery'] = Type::query()->orderBy(['name' => SORT_ASC]);
        }

        return $outputData;
    }
}