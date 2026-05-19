<?php

declare(strict_types=1);

/**
 * Create.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\SchemasMapping;

use Blackcube\Dcore\Enums\ElasticSchemaKind;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dcore\Models\SchemaSchema;
use Blackcube\Dboard\Handlers\Commons\AbstractCreate;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Forms\SchemaSchemaForm;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Router\CurrentRoute;

/**
 * SchemaSchema create action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Create extends AbstractCreate
{
    protected function getModelClass(): string { return SchemaSchema::class; }
    protected function getFormModelClass(): string { return SchemaSchemaForm::class; }
    protected function getEntityName(): string { return 'schemaSchema'; }
    protected function getViewPrefix(): string { return 'SchemasMapping'; }
    protected function getListRoute(): string { return 'dboard.xeo.mapping'; }
    protected function getSuccessRoute(): string { return 'dboard.xeo.mapping.edit'; }

    protected function primaryKeys(): array
    {
        return ['regularElasticSchemaId', 'xeoElasticSchemaId'];
    }

    /**
     * {@inheritdoc}
     * Override: pivot table has uninitialized int PKs on new instance,
     * so we initialize them to 0 before bridging.
     */
    protected function setupAction(): ?ResponseInterface
    {
        $actionModels = $this->getActionModels();

        foreach ($actionModels as $name => $actionModel) {
            if ($actionModel->hasModel()) {
                $model = new SchemaSchema();
                $model->setRegularElasticSchemaId(0);
                $model->setXeoElasticSchemaId(0);
                $this->models[$name] = $model;
            }
            if ($actionModel->hasFormModel()) {
                $formModel = $actionModel->getFormModel($this->models[$name]);
                if ($formModel !== null) {
                    $this->formModels[$name] = $formModel;
                }
            }
        }

        return null;
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
