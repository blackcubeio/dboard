<?php

declare(strict_types=1);

/**
 * Index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Types;

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dcore\Models\Type;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;
use Yiisoft\Router\CurrentRoute;

/**
 * Types index action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Index extends AbstractIndex
{
    protected function getModelClass(): string { return Type::class; }
    protected function getViewPrefix(): string { return 'Types'; }
    protected function getOrderBy(): array { return ['name' => SORT_ASC]; }

    /**
     * Override setupAction pour recherche sur 2 colonnes (name, handler).
     */
    protected function setupAction(): void
    {
        $modelClass = $this->getModelClass();
        $this->query = $modelClass::query()
            ->orderBy($this->getOrderBy());

        $search = $this->getSearchForm()->getSearch();
        if ($search !== '') {
            $this->query = $this->query->andWhere(['or',
                ['like', 'name', $search],
                ['like', 'handler', $search],
            ]);
        }

        $this->paginator = (new ActiveQueryPaginator($this->query))
            ->withPageSize($this->getPageSize())
            ->withCurrentPage((int) $this->getPageForm()->getPage());
    }
}
