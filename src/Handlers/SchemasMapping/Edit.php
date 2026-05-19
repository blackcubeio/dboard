<?php

declare(strict_types=1);

/**
 * Edit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\SchemasMapping;

use Blackcube\Dcore\Enums\ElasticSchemaKind;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dcore\Models\SchemaSchema;
use Blackcube\Dboard\Handlers\Commons\AbstractEdit;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Forms\SchemaSchemaForm;
use Yiisoft\Router\CurrentRoute;

/**
 * SchemaSchema edit action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Edit extends AbstractEdit
{
    protected function getModelClass(): string { return SchemaSchema::class; }
    protected function getFormModelClass(): string { return SchemaSchemaForm::class; }
    protected function getEntityName(): string { return 'schemaSchema'; }
    protected function getViewPrefix(): string { return 'SchemasMapping'; }
    protected function getListRoute(): string { return 'dboard.xeo.mapping'; }
    protected function stayOnPageAfterSave(): bool { return true; }

    protected function primaryKeys(): array
    {
        return ['regularElasticSchemaId', 'xeoElasticSchemaId'];
    }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();

        if ($outputData['type'] === OutputType::Render->value) {
            $outputData['data']['regularOptions'] = $this->getRegularOptions();
            $outputData['data']['xeoOptions'] = $this->getXeoOptions();
        }

        return $outputData;
    }

    private function getRegularOptions(): array
    {
        $options = [];
        $query = ElasticSchema::query()
            ->andWhere(['hidden' => false])
            ->andWhere(['not in', 'kind', [ElasticSchemaKind::Xeo->value]])
            ->orderBy(['name' => SORT_ASC]);
        foreach ($query->each() as $schema) {
            $options[$schema->getId()] = $schema->getName() . ' (' . $schema->getKind()->value . ')';
        }
        return $options;
    }

    private function getXeoOptions(): array
    {
        $options = [];
        $query = ElasticSchema::query()
            ->andWhere(['hidden' => false, 'kind' => ElasticSchemaKind::Xeo->value])
            ->orderBy(['name' => SORT_ASC]);
        foreach ($query->each() as $schema) {
            $options[$schema->getId()] = $schema->getName();
        }
        return $options;
    }
}
