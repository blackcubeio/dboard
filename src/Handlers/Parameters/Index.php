<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Parameters;

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dcore\Models\Parameter;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;
use Yiisoft\Router\CurrentRoute;

/**
 * Parameters index action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Index extends AbstractIndex
{
    protected function getModelClass(): string { return Parameter::class; }
    protected function getViewPrefix(): string { return 'Parameters'; }
    protected function getOrderBy(): array { return [
        'domain' => SORT_ASC,
        'name' => SORT_ASC,
    ]; }

    /**
     * Overrides setupAction to search on 3 columns (domain, name, value).
     */
    protected function setupAction(): void
    {
        $modelClass = $this->getModelClass();
        $this->query = $modelClass::query()
            ->orderBy($this->getOrderBy());

        $search = $this->getSearchForm()->getSearch();
        if ($search !== '') {
            $this->query = $this->query->andWhere([
                'or',
                [
                    'like',
                    'domain',
                    $search,
                ],
                [
                    'like',
                    'name',
                    $search,
                ],
                [
                    'like',
                    'value',
                    $search,
                ],
            ]);
        }

        $this->paginator = (new ActiveQueryPaginator($this->query))
            ->withPageSize($this->getPageSize())
            ->withCurrentPage((int) $this->getPageForm()->getPage());
    }
}
