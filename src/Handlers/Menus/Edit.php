<?php

declare(strict_types=1);

/**
 * Edit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Menus;

use Blackcube\Dcore\Models\Host;
use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Models\Menu;
use Blackcube\Dboard\Handlers\Commons\AbstractEdit;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Forms\MenuForm;

/**
 * Menu edit action.
 */
final class Edit extends AbstractEdit
{
    protected function getModelClass(): string { return Menu::class; }
    protected function getFormModelClass(): string { return MenuForm::class; }
    protected function getEntityName(): string { return 'menu'; }
    protected function getViewPrefix(): string { return 'Menus'; }
    protected function getListRoute(): string { return 'dboard.menus'; }
    protected function getMaxLevel(): int { return 4; }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();
        if ($outputData['type'] === OutputType::Render->value) {
            $outputData['data']['languageQuery'] = Language::query()->active()->orderBy(['name' => SORT_ASC]);
            $outputData['data']['hostQuery'] = Host::query()->orderBy(['name' => SORT_ASC]);
        }
        return $outputData;
    }
}
