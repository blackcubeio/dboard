<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Types;

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dcore\Models\Type;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;
use Yiisoft\Router\CurrentRoute;

/**
 * Types index action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Index extends AbstractIndex
{
    /** @var array<int, true> Types of the current page used by a content or a tag */
    protected array $usedTypeIds = [];

    protected function getModelClass(): string { return Type::class; }
    protected function getViewPrefix(): string { return 'Types'; }
    protected function getOrderBy(): array { return [
        'name' => SORT_ASC,
    ]; }

    /**
     * Overrides setupAction to search on 2 columns (name, handler).
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
                    'name',
                    $search,
                ],
                [
                    'like',
                    'handler',
                    $search,
                ],
            ]);
        }

        $this->paginator = (new ActiveQueryPaginator($this->query))
            ->withPageSize($this->getPageSize())
            ->withCurrentPage((int) $this->getPageForm()->getPage());
    }

    /**
     * Reads the current page and finds, in one query per table, the types used by a content or a tag.
     *
     * @return array<int, Type> The current page types
     */
    protected function eagerLoad(): array
    {
        $items = [];
        $typeIds = [];
        foreach ($this->paginator->read() as $type) {
            /** @var Type $type */
            $items[] = $type;
            $typeIds[] = $type->getId();
        }

        $this->usedTypeIds = [];
        if (empty($typeIds) === false) {
            $contentTypeIdsQuery = Content::query()
                ->select('typeId')
                ->distinct()
                ->andWhere([
                    'in',
                    'typeId',
                    $typeIds,
                ]);
            foreach ($contentTypeIdsQuery->column() as $usedTypeId) {
                $this->usedTypeIds[(int) $usedTypeId] = true;
            }
            $tagTypeIdsQuery = Tag::query()
                ->select('typeId')
                ->distinct()
                ->andWhere([
                    'in',
                    'typeId',
                    $typeIds,
                ]);
            foreach ($tagTypeIdsQuery->column() as $usedTypeId) {
                $this->usedTypeIds[(int) $usedTypeId] = true;
            }
        }

        return $items;
    }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();
        $outputData['data']['usedTypeIds'] = $this->usedTypeIds;
        return $outputData;
    }
}
