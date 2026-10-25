<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\SchemasMapping;

use Blackcube\Dcore\Models\SchemaSchema;
use Blackcube\Dboard\Handlers\Commons\AbstractDelete;

/**
 * SchemaSchema delete action (DELETE).
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Delete extends AbstractDelete
{
    protected function getModelClass(): string { return SchemaSchema::class; }
    protected function getEntityName(): string { return 'mapping'; }
    protected function getListId(): string { return 'mapping-list'; }
    protected function getListRoute(): string { return 'dboard.xeo.mapping'; }
    protected function getSuccessMessage(): string { return $this->translator->translate('Mapping "{name}" has been deleted.', [
        'name' => $this->getModelName(),
    ], 'dboard-modules'); }

    protected function primaryKeys(): array
    {
        return [
            'regularElasticSchemaId',
            'xeoElasticSchemaId',
        ];
    }

    protected function getModelName(): string
    {
        $model = $this->models['main'];
        $regular = $model->regularElasticSchema;
        $xeo = $model->xeoElasticSchema;
        return ($regular !== null ? $regular->getName() : '?') . ' → ' . ($xeo !== null ? $xeo->getName() : '?');
    }
}
