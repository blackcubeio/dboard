<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Dboard\DataServices\DashboardDataService;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dcore\Models\Type;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
use Yiisoft\Router\UrlGeneratorInterface;

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
        private readonly UrlGeneratorInterface $urlGenerator,
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

    /**
     * The records behind a health counter, the first ones by name, each with
     * the link that puts it right; the total says how many there are.
     *
     * @return array{items: array<int, array{label: string, url: string, detail: string|null}>, total: int}
     */
    public function getHealthItems(string $kind, int $limit): array
    {
        $items = [];
        $total = 0;
        switch ($kind) {
            case 'unreachable':
                $unreachableQuery = $this->dashboardDs->queryUnreachable();
                $total = $unreachableQuery->count();
                $items = $this->contentItems($unreachableQuery, $limit);
                break;
            case 'without-xeo':
                $withoutXeoQuery = $this->dashboardDs->queryPagesWithoutXeo();
                $total = $withoutXeoQuery->count();
                $items = $this->pageItems($withoutXeoQuery, $limit);
                break;
            case 'out-of-sitemap':
                $outOfSitemapQuery = $this->dashboardDs->queryPagesOutOfSitemap();
                $total = $outOfSitemapQuery->count();
                $items = $this->pageItems($outOfSitemapQuery, $limit);
                break;
            case 'redirections':
                $redirectionsQuery = $this->dashboardDs->queryRedirections();
                $total = $redirectionsQuery->count();
                $items = $this->slugItems($redirectionsQuery, $limit);
                break;
            case 'inactive-slugs':
                $inactiveSlugsQuery = $this->dashboardDs->queryInactiveSlugs();
                $total = $inactiveSlugsQuery->count();
                $items = $this->slugItems($inactiveSlugsQuery, $limit);
                break;
            case 'missing-translations':
                $languagesCount = $this->getCountActiveLanguages();
                $withoutGroupQuery = $this->dashboardDs->queryWithoutTranslationGroup();
                $inShortGroupQuery = $this->dashboardDs->queryInShortTranslationGroup($languagesCount);
                $total = $withoutGroupQuery->count() + $inShortGroupQuery->count();
                $items = array_slice(array_merge(
                    $this->contentItems($withoutGroupQuery, $limit),
                    $this->contentItems($inShortGroupQuery, $limit),
                ), 0, $limit);
                break;
            default:
                break;
        }
        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    /**
     * Contents as items: the name, the edit screen, the language.
     *
     * @return array<int, array{label: string, url: string, detail: string|null}>
     */
    protected function contentItems(ActiveQueryInterface $query, int $limit): array
    {
        $contentsQuery = (clone $query)
            ->orderBy([
                'name' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->limit($limit);
        $items = [];
        /** @var Content $content */
        foreach ($contentsQuery->each() as $content) {
            $items[] = [
                'label' => $content->getName() ?? '#' . $content->getId(),
                'url' => $this->urlGenerator->generate('dboard.contents.edit', [
                    'id' => $content->getId(),
                ]),
                'detail' => $content->getLanguageId(),
            ];
        }
        return $items;
    }

    /**
     * Pages as items: the content or the tag that owns the slug, its edit
     * screen where the *EO and the sitemap are set, else the slug itself.
     *
     * @return array<int, array{label: string, url: string, detail: string|null}>
     */
    protected function pageItems(ActiveQueryInterface $query, int $limit): array
    {
        $slugsQuery = (clone $query)
            ->with([
                'content',
                'tag',
            ])
            ->orderBy([
                'path' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->limit($limit);
        $items = [];
        /** @var Slug $slug */
        foreach ($slugsQuery->each() as $slug) {
            $content = $slug->content;
            $tag = $slug->tag;
            if ($content !== null) {
                $label = $content->getName() ?? '/' . $slug->getPath();
                $url = $this->urlGenerator->generate('dboard.contents.edit', [
                    'id' => $content->getId(),
                ]);
            } elseif ($tag !== null) {
                $label = $tag->getName();
                $url = $this->urlGenerator->generate('dboard.tags.edit', [
                    'id' => $tag->getId(),
                ]);
            } else {
                $label = '/' . $slug->getPath();
                $url = $this->urlGenerator->generate('dboard.slugs.edit', [
                    'id' => $slug->getId(),
                ]);
            }
            $items[] = [
                'label' => $label,
                'url' => $url,
                'detail' => '/' . $slug->getPath(),
            ];
        }
        return $items;
    }

    /**
     * Slugs as items: the path, the edit screen, the target when it sends elsewhere.
     *
     * @return array<int, array{label: string, url: string, detail: string|null}>
     */
    protected function slugItems(ActiveQueryInterface $query, int $limit): array
    {
        $slugsQuery = (clone $query)
            ->orderBy([
                'path' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->limit($limit);
        $items = [];
        /** @var Slug $slug */
        foreach ($slugsQuery->each() as $slug) {
            $items[] = [
                'label' => '/' . $slug->getPath(),
                'url' => $this->urlGenerator->generate('dboard.slugs.edit', [
                    'id' => $slug->getId(),
                ]),
                'detail' => $slug->getTargetUrl(),
            ];
        }
        return $items;
    }
}
