<?php

declare(strict_types=1);

/**
 * Delete.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Languages;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Models\Menu;
use Blackcube\Dboard\Handlers\Commons\AbstractDelete;

/**
 * Language delete action (DELETE only).
 * Blocks deletion if language is used by Content or Menu.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Delete extends AbstractDelete
{
    protected function getModelClass(): string { return Language::class; }
    protected function getEntityName(): string { return 'language'; }
    protected function getEntityLabel(): string { return 'language'; }
    protected function getListId(): string { return 'languages-list'; }
    protected function getListRoute(): string { return 'dboard.languages'; }

    /**
     * Check if language is used by Content or Menu.
     *
     * @return array{contents: int, menus: int} Usage counts
     */
    private function getUsageCounts(): array
    {
        $languageId = $this->models['main']->getId();
        return [
            'contents' => Content::query()->andWhere(['languageId' => $languageId])->count(),
            'menus' => Menu::query()->andWhere(['languageId' => $languageId])->count(),
        ];
    }

    protected function beforeDelete(bool $inTransaction): void
    {
        if ($inTransaction) {
            return;
        }

        $usage = $this->getUsageCounts();
        if ($usage['contents'] > 0 || $usage['menus'] > 0) {
            throw new \RuntimeException('Cannot delete language. It is used by ' . $usage['contents'] . ' content(s) and ' . $usage['menus'] . ' menu(s).');
        }
    }
}