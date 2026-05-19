<?php

declare(strict_types=1);

/**
 * Index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Slugs;

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;
use Yiisoft\Router\CurrentRoute;

/**
 * Slugs index action - only redirections (targetUrl and httpCode not null).
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Index extends AbstractIndex
{
    protected function getModelClass(): string { return Slug::class; }
    protected function getViewPrefix(): string { return 'Slugs'; }
    protected function getOrderBy(): array { return ['id' => SORT_DESC]; }

    /**
     * Override setupAction pour filtrer uniquement les redirections.
     */
    protected function setupAction(): void
    {
        $this->query = Slug::query()
            ->andWhere(['IS NOT', 'targetUrl', null])
            ->andWhere(['IS NOT', 'httpCode', null])
            ->orderBy($this->getOrderBy());

        $search = $this->getSearchForm()->getSearch();
        if ($search !== '') {
            $this->query = $this->query->andWhere(['like', 'path', $search]);
        }

        $this->paginator = (new ActiveQueryPaginator($this->query))
            ->withPageSize($this->getPageSize())
            ->withCurrentPage((int) $this->getPageForm()->getPage());
    }
}
