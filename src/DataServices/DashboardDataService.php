<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\DataServices;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Models\Sitemap;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dcore\Models\Type;
use Blackcube\Dcore\Models\Xeo;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
use Yiisoft\Db\Expression\Expression;

/**
 * The queries behind the numbers of the dashboard, each one returned open, for
 * the service to count or read.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class DashboardDataService
{
    /**
     * Active contents inside their publication window.
     */
    public function queryActive(): ActiveQueryInterface
    {
        return Content::query()->active()->available();
    }

    /**
     * Active contents whose publication starts later.
     */
    public function queryScheduled(): ActiveQueryInterface
    {
        return Content::query()
            ->active()
            ->andWhere([
                '>',
                'dateStart',
                new Expression('NOW()'),
            ]);
    }

    /**
     * Active contents whose publication has ended.
     */
    public function queryExpired(): ActiveQueryInterface
    {
        return Content::query()
            ->active()
            ->andWhere([
                '<',
                'dateEnd',
                new Expression('NOW()'),
            ]);
    }

    public function queryInactive(): ActiveQueryInterface
    {
        return Content::query()->active(active: false);
    }

    public function queryTagsActive(): ActiveQueryInterface
    {
        return Tag::query()->active();
    }

    public function queryTagsInactive(): ActiveQueryInterface
    {
        return Tag::query()->active(active: false);
    }

    /**
     * Contents updated within the last days, on the clock of the database, the
     * one that wrote the dates.
     */
    public function queryContentsUpdatedWithin(int $days): ActiveQueryInterface
    {
        return Content::query()
            ->andWhere([
                '>=',
                'dateUpdate',
                new Expression('NOW() - INTERVAL :days DAY', [
                    ':days' => $days,
                ]),
            ]);
    }

    public function queryTagsUpdatedWithin(int $days): ActiveQueryInterface
    {
        return Tag::query()
            ->andWhere([
                '>=',
                'dateUpdate',
                new Expression('NOW() - INTERVAL :days DAY', [
                    ':days' => $days,
                ]),
            ]);
    }

    /**
     * Typed contents without a slug: they exist, nobody can reach them.
     */
    public function queryUnreachable(): ActiveQueryInterface
    {
        return Content::query()
            ->andWhere([
                'not',
                [
                    'typeId' => null,
                ],
            ])
            ->andWhere([
                'slugId' => null,
            ]);
    }

    /**
     * The slugs that are pages: active, not redirections.
     */
    public function queryPages(): ActiveQueryInterface
    {
        return Slug::query()
            ->active()
            ->andWhere([
                'targetUrl' => null,
            ]);
    }

    /**
     * Pages without an active *EO record.
     */
    public function queryPagesWithoutXeo(): ActiveQueryInterface
    {
        $xeoSlugsQuery = Xeo::query()->active()->select('slugId');
        return $this->queryPages()
            ->andWhere([
                'not in',
                'id',
                $xeoSlugsQuery,
            ]);
    }

    /**
     * Pages without an active sitemap entry.
     */
    public function queryPagesOutOfSitemap(): ActiveQueryInterface
    {
        $sitemapSlugsQuery = Sitemap::query()->active()->select('slugId');
        return $this->queryPages()
            ->andWhere([
                'not in',
                'id',
                $sitemapSlugsQuery,
            ]);
    }

    /**
     * Slugs that send elsewhere.
     */
    public function queryRedirections(): ActiveQueryInterface
    {
        return Slug::query()
            ->andWhere([
                'not',
                [
                    'targetUrl' => null,
                ],
            ]);
    }

    public function queryInactiveSlugs(): ActiveQueryInterface
    {
        return Slug::query()->active(active: false);
    }

    public function queryActiveLanguages(): ActiveQueryInterface
    {
        return Language::query()->active();
    }

    public function queryLanguages(): ActiveQueryInterface
    {
        return Language::query();
    }

    public function queryTypes(): ActiveQueryInterface
    {
        return Type::query();
    }

    /**
     * Active contents without a translation group.
     */
    public function queryWithoutTranslationGroup(): ActiveQueryInterface
    {
        return Content::query()
            ->active()
            ->andWhere([
                'translationGroupId' => null,
            ]);
    }

    /**
     * Active contents whose translation group has fewer members than the
     * given number of languages.
     */
    public function queryInShortTranslationGroup(int $languagesCount): ActiveQueryInterface
    {
        $shortGroupsQuery = Content::query()
            ->select('translationGroupId')
            ->andWhere([
                'not',
                [
                    'translationGroupId' => null,
                ],
            ])
            ->groupBy([
                'translationGroupId',
            ])
            ->having(new Expression('COUNT(*) < :languagesCount', [
                ':languagesCount' => $languagesCount,
            ]));
        return Content::query()
            ->active()
            ->andWhere([
                'in',
                'translationGroupId',
                $shortGroupsQuery,
            ]);
    }

    /**
     * Active contents counted by language, as rows of languageId and total,
     * the most filled first.
     */
    public function queryCountsByLanguage(): ActiveQueryInterface
    {
        return Content::query()
            ->active()
            ->select([
                'languageId',
                'total' => new Expression('COUNT(*)'),
            ])
            ->groupBy([
                'languageId',
            ])
            ->orderBy([
                'total' => SORT_DESC,
                'languageId' => SORT_ASC,
            ])
            ->asArray();
    }

    /**
     * Active contents counted by type, as rows of typeId and total, the most
     * filled first.
     */
    public function queryCountsByType(): ActiveQueryInterface
    {
        return Content::query()
            ->active()
            ->select([
                'typeId',
                'total' => new Expression('COUNT(*)'),
            ])
            ->groupBy([
                'typeId',
            ])
            ->orderBy([
                'total' => SORT_DESC,
                'typeId' => SORT_ASC,
            ])
            ->asArray();
    }
}
