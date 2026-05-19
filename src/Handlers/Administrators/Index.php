<?php

declare(strict_types=1);

/**
 * Index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Administrators;

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;
use Blackcube\Dboard\Models\Administrator;
use Yiisoft\Router\CurrentRoute;

/**
 * Administrators index action.
 */
final class Index extends AbstractIndex
{
    protected function getModelClass(): string
    {
        return Administrator::class;
    }

    protected function getViewPrefix(): string
    {
        return 'Administrators';
    }

    protected function getOrderBy(): array
    {
        return ['lastname' => SORT_ASC, 'firstname' => SORT_ASC];
    }

    protected function setupAction(): void
    {
        $modelClass = $this->getModelClass();
        $this->query = $modelClass::query()
            ->orderBy($this->getOrderBy());

        $search = $this->getSearchForm()->getSearch();
        if ($search !== '') {
            $this->query = $this->query->andWhere(['or',
                ['like', 'firstname', $search],
                ['like', 'lastname', $search],
                ['like', 'email', $search],
            ]);
        }

        $this->paginator = (new ActiveQueryPaginator($this->query))
            ->withPageSize($this->getPageSize())
            ->withCurrentPage((int) $this->getPageForm()->getPage());
    }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();
        $outputData['data']['currentUserId'] = $this->request->getAttribute('userId');
        return $outputData;
    }
}
