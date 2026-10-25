<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Languages;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Models\Menu;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;

/**
 * Languages index action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Index extends AbstractIndex
{
    /** @var array<string, true> Languages of the current page used by a content or a menu */
    protected array $usedLanguageIds = [];

    protected function getModelClass(): string { return Language::class; }
    protected function getViewPrefix(): string { return 'Languages'; }
    protected function getOrderBy(): array { return [
        'name' => SORT_ASC,
    ]; }
    protected function getSearchColumn(): string { return 'name'; }

    /**
     * Reads the current page and finds, in one query per table, the languages used by a content or a menu.
     *
     * @return array<int, Language> The current page languages
     */
    protected function eagerLoad(): array
    {
        $items = [];
        $languageIds = [];
        foreach ($this->paginator->read() as $language) {
            /** @var Language $language */
            $items[] = $language;
            $languageIds[] = $language->getId();
        }

        $this->usedLanguageIds = [];
        if (empty($languageIds) === false) {
            $contentLanguageIdsQuery = Content::query()
                ->select('languageId')
                ->distinct()
                ->andWhere([
                    'in',
                    'languageId',
                    $languageIds,
                ]);
            foreach ($contentLanguageIdsQuery->column() as $usedLanguageId) {
                $this->usedLanguageIds[(string) $usedLanguageId] = true;
            }
            $menuLanguageIdsQuery = Menu::query()
                ->select('languageId')
                ->distinct()
                ->andWhere([
                    'in',
                    'languageId',
                    $languageIds,
                ]);
            foreach ($menuLanguageIdsQuery->column() as $usedLanguageId) {
                $this->usedLanguageIds[(string) $usedLanguageId] = true;
            }
        }

        return $items;
    }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();
        $outputData['data']['usedLanguageIds'] = $this->usedLanguageIds;
        return $outputData;
    }
}
