<?php

declare(strict_types=1);

/**
 * Index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\ElasticSchemas;

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;
use Yiisoft\Router\CurrentRoute;

/**
 * ElasticSchemas index action.
 * Builtin schemas are excluded from the listing.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Index extends AbstractIndex
{
    protected function getModelClass(): string { return ElasticSchema::class; }
    protected function getViewPrefix(): string { return 'ElasticSchemas'; }
    protected function getOrderBy(): array { return ['kind' => SORT_ASC, 'order' => SORT_ASC]; }
    protected function getSearchColumn(): string { return 'name'; }

    protected function setupAction(): void
    {
        parent::setupAction();
        $this->query->andWhere(['hidden' => false]);
        $this->paginator = (new ActiveQueryPaginator($this->query))
            ->withPageSize($this->getPageSize())
            ->withCurrentPage((int) $this->getPageForm()->getPage());
    }
}