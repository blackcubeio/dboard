<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Slugs;

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dcore\Models\Host;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;
use Yiisoft\Router\CurrentRoute;

/**
 * Slugs index action - only redirections (targetUrl and httpCode not null).
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Index extends AbstractIndex
{
    protected function getModelClass(): string { return Slug::class; }
    protected function getViewPrefix(): string { return 'Slugs'; }
    protected function getOrderBy(): array { return [
        'id' => SORT_DESC,
    ]; }

    /**
     * Overrides setupAction to keep redirections only.
     */
    protected function setupAction(): void
    {
        $this->query = Slug::query()
            ->andWhere([
                'IS NOT',
                'targetUrl',
                null,
            ])
            ->andWhere([
                'IS NOT',
                'httpCode',
                null,
            ])
            ->orderBy($this->getOrderBy());

        $search = $this->getSearchForm()->getSearch();
        if ($search !== '') {
            $this->query = $this->query->andWhere([
                'like',
                'path',
                $search,
            ]);
        }

        $this->paginator = (new ActiveQueryPaginator($this->query))
            ->withPageSize($this->getPageSize())
            ->withCurrentPage((int) $this->getPageForm()->getPage());
    }

    /**
     * Eager-loads the host of each slug for the current page, removing the per-row N+1.
     *
     * @return array<int, Slug> The current page slugs, host preloaded
     */
    protected function eagerLoad(): array
    {
        $slugs = [];
        $hostIds = [];
        foreach ($this->paginator->read() as $slug) {
            /** @var Slug $slug */
            $slugs[] = $slug;
            $hostIds[$slug->getHostId()] = $slug->getHostId();
        }

        $hosts = $this->getIndexedRecords(Host::class, $hostIds);

        foreach ($slugs as $slug) {
            $slug->populateRelation('host', $hosts[$slug->getHostId()] ?? null);
        }

        return $slugs;
    }
}
