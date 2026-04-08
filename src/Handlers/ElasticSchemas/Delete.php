<?php

declare(strict_types=1);

/**
 * Delete.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\ElasticSchemas;

use Blackcube\Dcore\Models\Bloc;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Handlers\Commons\AbstractDelete;

/**
 * ElasticSchema delete action (DELETE only).
 * Blocks deletion if schema is used by Bloc, Content or Tag.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Delete extends AbstractDelete
{
    protected function getModelClass(): string { return ElasticSchema::class; }
    protected function getEntityName(): string { return 'elasticSchema'; }
    protected function getEntityLabel(): string { return 'schema'; }
    protected function getListId(): string { return 'elasticschemas-list'; }
    protected function getListRoute(): string { return 'dboard.elasticschemas'; }

    /**
     * Check if schema is used by Bloc, Content or Tag.
     *
     * @return array{blocs: int, contents: int, tags: int} Usage counts
     */
    private function getUsageCounts(): array
    {
        $schemaId = $this->models['main']->getId();
        return [
            'blocs' => Bloc::query()->andWhere(['elasticSchemaId' => $schemaId])->count(),
            'contents' => Content::query()->andWhere(['elasticSchemaId' => $schemaId])->count(),
            'tags' => Tag::query()->andWhere(['elasticSchemaId' => $schemaId])->count(),
        ];
    }

    protected function beforeDelete(bool $inTransaction): void
    {
        if ($inTransaction) {
            return;
        }

        $usage = $this->getUsageCounts();
        if ($usage['blocs'] > 0 || $usage['contents'] > 0 || $usage['tags'] > 0) {
            throw new \RuntimeException('Cannot delete schema. It is used by ' . $usage['blocs'] . ' block(s), ' . $usage['contents'] . ' content(s) and ' . $usage['tags'] . ' tag(s).');
        }
    }
}