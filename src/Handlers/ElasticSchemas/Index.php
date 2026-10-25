<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\ElasticSchemas;

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dcore\Models\Bloc;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;
use Yiisoft\Router\CurrentRoute;

/**
 * ElasticSchemas index action.
 * Builtin schemas are excluded from the listing.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Index extends AbstractIndex
{
    /** @var array<int, true> Elastic schemas of the current page used by a bloc, a content or a tag */
    protected array $usedElasticSchemaIds = [];

    protected function getModelClass(): string { return ElasticSchema::class; }
    protected function getViewPrefix(): string { return 'ElasticSchemas'; }
    protected function getOrderBy(): array { return [
        'kind' => SORT_ASC,
        'order' => SORT_ASC,
    ]; }
    protected function getSearchColumn(): string { return 'name'; }

    protected function setupAction(): void
    {
        parent::setupAction();
        $this->query->andWhere([
            'hidden' => false,
        ]);
        $this->paginator = (new ActiveQueryPaginator($this->query))
            ->withPageSize($this->getPageSize())
            ->withCurrentPage((int) $this->getPageForm()->getPage());
    }

    /**
     * Reads the current page and finds, in one query per table, the elastic schemas used by a bloc, a content or a tag.
     *
     * @return array<int, ElasticSchema> The current page elastic schemas
     */
    protected function eagerLoad(): array
    {
        $items = [];
        $elasticSchemaIds = [];
        foreach ($this->paginator->read() as $elasticSchema) {
            /** @var ElasticSchema $elasticSchema */
            $items[] = $elasticSchema;
            $elasticSchemaIds[] = $elasticSchema->getId();
        }

        $this->usedElasticSchemaIds = [];
        if (empty($elasticSchemaIds) === false) {
            $blocElasticSchemaIdsQuery = Bloc::query()
                ->select('elasticSchemaId')
                ->distinct()
                ->andWhere([
                    'in',
                    'elasticSchemaId',
                    $elasticSchemaIds,
                ]);
            foreach ($blocElasticSchemaIdsQuery->column() as $usedElasticSchemaId) {
                $this->usedElasticSchemaIds[(int) $usedElasticSchemaId] = true;
            }
            $contentElasticSchemaIdsQuery = Content::query()
                ->select('elasticSchemaId')
                ->distinct()
                ->andWhere([
                    'in',
                    'elasticSchemaId',
                    $elasticSchemaIds,
                ]);
            foreach ($contentElasticSchemaIdsQuery->column() as $usedElasticSchemaId) {
                $this->usedElasticSchemaIds[(int) $usedElasticSchemaId] = true;
            }
            $tagElasticSchemaIdsQuery = Tag::query()
                ->select('elasticSchemaId')
                ->distinct()
                ->andWhere([
                    'in',
                    'elasticSchemaId',
                    $elasticSchemaIds,
                ]);
            foreach ($tagElasticSchemaIdsQuery->column() as $usedElasticSchemaId) {
                $this->usedElasticSchemaIds[(int) $usedElasticSchemaId] = true;
            }
        }

        return $items;
    }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();
        $outputData['data']['usedElasticSchemaIds'] = $this->usedElasticSchemaIds;
        return $outputData;
    }
}