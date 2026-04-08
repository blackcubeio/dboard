<?php

declare(strict_types=1);

/**
 * Delete.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\SchemasMapping;

use Blackcube\Dcore\Models\SchemaSchema;
use Blackcube\Dboard\Handlers\Commons\AbstractDelete;

/**
 * SchemaSchema delete action (DELETE).
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Delete extends AbstractDelete
{
    protected function getModelClass(): string { return SchemaSchema::class; }
    protected function getEntityName(): string { return 'mapping'; }
    protected function getListId(): string { return 'mapping-list'; }
    protected function getListRoute(): string { return 'dboard.xeo.mapping'; }

    protected function primaryKeys(): array
    {
        return ['regularElasticSchemaId', 'xeoElasticSchemaId'];
    }

    protected function getModelName(): string
    {
        $model = $this->models['main'];
        $regular = $model->relation('regularElasticSchema');
        $xeo = $model->relation('xeoElasticSchema');
        return ($regular ? $regular->getName() : '?') . ' → ' . ($xeo ? $xeo->getName() : '?');
    }
}
