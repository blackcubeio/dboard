<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Dboard\DataServices\DashboardDataService;
use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Models\Type;

/**
 * The numbers of the dashboard, each one counted or read from a query of the
 * data service when the page is drawn. The lists of the page stay in its handler.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class DashboardService
{
    public function __construct(
        private readonly DashboardDataService $dashboardDs,
    ) {
    }

    public function getCountActive(): int
    {
        return $this->dashboardDs->queryActive()->count();
    }

    public function getCountScheduled(): int
    {
        return $this->dashboardDs->queryScheduled()->count();
    }

    public function getCountExpired(): int
    {
        return $this->dashboardDs->queryExpired()->count();
    }

    public function getCountInactive(): int
    {
        return $this->dashboardDs->queryInactive()->count();
    }

    public function getCountTagsActive(): int
    {
        return $this->dashboardDs->queryTagsActive()->count();
    }

    public function getCountTagsInactive(): int
    {
        return $this->dashboardDs->queryTagsInactive()->count();
    }

    /**
     * Contents and tags updated within the last days.
     */
    public function getCountUpdatedWithin(int $days): int
    {
        $contentsCount = $this->dashboardDs->queryContentsUpdatedWithin($days)->count();
        $tagsCount = $this->dashboardDs->queryTagsUpdatedWithin($days)->count();
        return $contentsCount + $tagsCount;
    }

    public function getCountUnreachable(): int
    {
        return $this->dashboardDs->queryUnreachable()->count();
    }

    public function getCountPagesWithoutXeo(): int
    {
        return $this->dashboardDs->queryPagesWithoutXeo()->count();
    }

    public function getCountPagesOutOfSitemap(): int
    {
        return $this->dashboardDs->queryPagesOutOfSitemap()->count();
    }

    public function getCountRedirections(): int
    {
        return $this->dashboardDs->queryRedirections()->count();
    }

    public function getCountInactiveSlugs(): int
    {
        return $this->dashboardDs->queryInactiveSlugs()->count();
    }

    public function getCountActiveLanguages(): int
    {
        return $this->dashboardDs->queryActiveLanguages()->count();
    }

    /**
     * Active contents not translated in every active language: without a
     * translation group, or in a group shorter than the languages.
     */
    public function getCountMissingTranslations(): int
    {
        $languagesCount = $this->getCountActiveLanguages();
        $withoutGroupCount = $this->dashboardDs->queryWithoutTranslationGroup()->count();
        $inShortGroupCount = $this->dashboardDs->queryInShortTranslationGroup($languagesCount)->count();
        return $withoutGroupCount + $inShortGroupCount;
    }

    /**
     * Active contents by language, the most filled first.
     *
     * @return array<int, array{name: string, total: int}>
     */
    public function getCountsByLanguage(): array
    {
        $languageNames = [];
        $languagesQuery = $this->dashboardDs->queryLanguages();
        /** @var Language $language */
        foreach ($languagesQuery->each() as $language) {
            $languageNames[$language->getId()] = $language->getName();
        }
        $countsQuery = $this->dashboardDs->queryCountsByLanguage();
        $counts = [];
        /** @var array{languageId: string|null, total: int|string} $languageCount */
        foreach ($countsQuery->each() as $languageCount) {
            $counts[] = [
                'name' => $languageNames[$languageCount['languageId']] ?? (string) $languageCount['languageId'],
                'total' => (int) $languageCount['total'],
            ];
        }
        return $counts;
    }

    /**
     * Active contents by type, the most filled first; the contents without a
     * type come with a null name.
     *
     * @return array<int, array{name: string|null, total: int}>
     */
    public function getCountsByType(): array
    {
        $typeNames = [];
        $typesQuery = $this->dashboardDs->queryTypes();
        /** @var Type $type */
        foreach ($typesQuery->each() as $type) {
            $typeNames[$type->getId()] = $type->getName();
        }
        $countsQuery = $this->dashboardDs->queryCountsByType();
        $counts = [];
        /** @var array{typeId: int|string|null, total: int|string} $typeCount */
        foreach ($countsQuery->each() as $typeCount) {
            $typeName = null;
            if ($typeCount['typeId'] !== null) {
                $typeName = $typeNames[$typeCount['typeId']] ?? (string) $typeCount['typeId'];
            }
            $counts[] = [
                'name' => $typeName,
                'total' => (int) $typeCount['total'],
            ];
        }
        return $counts;
    }
}
