<?php

declare(strict_types=1);

/**
 * Create.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Menus;

use Blackcube\Dcore\Models\Host;
use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Models\Menu;
use Blackcube\Dboard\Handlers\Commons\AbstractCreate;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Enums\TreePosition;
use Blackcube\Dboard\Models\Forms\MenuForm;

/**
 * Menu create action.
 */
final class Create extends AbstractCreate
{
    protected function getModelClass(): string { return Menu::class; }
    protected function getFormModelClass(): string { return MenuForm::class; }
    protected function getEntityName(): string { return 'menu'; }
    protected function getViewPrefix(): string { return 'Menus'; }
    protected function getListRoute(): string { return 'dboard.menus'; }
    protected function getSuccessRoute(): string { return 'dboard.menus.edit'; }
    protected function getMaxLevel(): int { return 4; }

    protected function getDefaultMoveTarget(): ?array
    {
        $last = Menu::query()->orderBy(['dateCreate' => SORT_DESC])->one();
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
            $outputData['data']['hostQuery'] = Host::query()->orderBy(['name' => SORT_ASC]);
        }
        return $outputData;
    }
}
