<?php

declare(strict_types=1);

/**
 * Edit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\ElasticSchemas;

use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dcore\Models\Type;
use Blackcube\Dcore\Models\TypeElasticSchema;
use Blackcube\Dboard\Handlers\Commons\AbstractEdit;
use Blackcube\Dboard\Models\Forms\ElasticSchemaForm;
use Blackcube\Dboard\Models\Forms\ElasticSchemaTypeForm;
use Blackcube\BridgeModel\BridgeFormModel;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * ElasticSchema edit action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Edit extends AbstractEdit
{
    /** @var ElasticSchemaTypeForm[] */
    protected array $elasticSchemaTypes = [];

    protected function getModelClass(): string { return ElasticSchema::class; }
    protected function getFormModelClass(): string { return ElasticSchemaForm::class; }
    protected function getEntityName(): string { return 'elasticSchema'; }
    protected function getViewPrefix(): string { return 'ElasticSchemas'; }
    protected function getListRoute(): string { return 'dboard.elasticschemas'; }

    protected function setupAction(): ?ResponseInterface
    {
        $response = parent::setupAction();
        if ($response !== null) {
            return $response;
        }

        $model = $this->models['main'];
        $elasticSchemaId = $model->getId();

        if ($model->isBuiltin()) {
            $this->formModels['main']->setScenario('builtin');
        }

        $typesQuery = Type::query()->orderBy(['name' => SORT_ASC]);
        $this->elasticSchemaTypes = [];

        foreach ($typesQuery->each() as $type) {
            $typeElasticSchema = TypeElasticSchema::query()
                ->andWhere(['elasticSchemaId' => $elasticSchemaId, 'typeId' => $type->getId()])
                ->one();

            $form = new ElasticSchemaTypeForm(translator: $this->translator);
            if ($typeElasticSchema !== null) {
                $form->initFromModel($typeElasticSchema);
                $form->setAllowed(true);
            }
            $form->setElasticSchemaId($elasticSchemaId);
            $form->setTypeId($type->getId());
            $form->setTypeName($type->getName());
            $form->setScenario('edit');
            $this->elasticSchemaTypes[$type->getId()] = $form;
        }

        if ($this->request->getMethod() === Method::POST) {
            BridgeFormModel::loadMultiple($this->elasticSchemaTypes, $this->getBodyParams());
        }

        return null;
    }

    protected function afterSave(bool $inTransaction): void
    {
        if (!$inTransaction) {
            return;
        }

        $elasticSchemaId = $this->models['main']->getId();
        foreach ($this->elasticSchemaTypes as $form) {
            $typeId = (int) $form->getTypeId();
            $allowed = $form->isAllowed();

            $typeElasticSchema = TypeElasticSchema::query()
                ->andWhere(['elasticSchemaId' => $elasticSchemaId, 'typeId' => $typeId])
                ->one();

            if ($allowed && $typeElasticSchema === null) {
                $typeElasticSchema = new TypeElasticSchema();
                $typeElasticSchema->setElasticSchemaId($elasticSchemaId);
                $typeElasticSchema->setTypeId($typeId);
                $typeElasticSchema->save();
            } elseif (!$allowed && $typeElasticSchema !== null) {
                $typeElasticSchema->delete();
            }
        }
    }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();

        if (isset($outputData['data']) && $outputData['type'] !== 'redirect') {
            $outputData['data']['allowedTypes'] = $this->elasticSchemaTypes;
        }

        return $outputData;
    }
}