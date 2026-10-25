<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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
use Blackcube\BridgeFormModel\BridgeFormModel;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * ElasticSchema edit action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Edit extends AbstractEdit
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
        $setupResponse = parent::setupAction();
        if ($setupResponse === null) {
            $model = $this->models['main'];
            $elasticSchemaId = $model->getId();

            if ($model->isBuiltin() === true) {
                $this->formModels['main']->setScenario('builtin');
            }

            $typesQuery = Type::query()->orderBy([
                'name' => SORT_ASC,
            ]);
            $typeElasticSchemaPivotsQuery = TypeElasticSchema::query()
                ->andWhere([
                    'elasticSchemaId' => $elasticSchemaId,
                ]);
            $typeElasticSchemaPivots = [];
            /** @var TypeElasticSchema $typeElasticSchemaPivot */
            foreach ($typeElasticSchemaPivotsQuery->each() as $typeElasticSchemaPivot) {
                $typeElasticSchemaPivots[$typeElasticSchemaPivot->getTypeId()] = $typeElasticSchemaPivot;
            }
            $this->elasticSchemaTypes = [];

            /** @var Type $type */
            foreach ($typesQuery->each() as $type) {
                $typeElasticSchema = $typeElasticSchemaPivots[$type->getId()] ?? null;

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
        }

        return $setupResponse;
    }

    protected function afterSave(bool $inTransaction): void
    {
        if ($inTransaction === true) {
            $elasticSchemaId = $this->models['main']->getId();
            $typeElasticSchemaPivotsQuery = TypeElasticSchema::query()
                ->andWhere([
                    'elasticSchemaId' => $elasticSchemaId,
                ]);
            $typeElasticSchemaPivots = [];
            /** @var TypeElasticSchema $typeElasticSchemaPivot */
            foreach ($typeElasticSchemaPivotsQuery->each() as $typeElasticSchemaPivot) {
                $typeElasticSchemaPivots[$typeElasticSchemaPivot->getTypeId()] = $typeElasticSchemaPivot;
            }
            foreach ($this->elasticSchemaTypes as $form) {
                $typeId = (int) $form->getTypeId();
                $allowed = $form->isAllowed();

                $typeElasticSchema = $typeElasticSchemaPivots[$typeId] ?? null;

                if ($allowed === true && $typeElasticSchema === null) {
                    $typeElasticSchema = new TypeElasticSchema();
                    $typeElasticSchema->setElasticSchemaId($elasticSchemaId);
                    $typeElasticSchema->setTypeId($typeId);
                    $typeElasticSchema->save();
                } elseif ($allowed === false && $typeElasticSchema !== null) {
                    $typeElasticSchema->delete();
                }
            }
        }
    }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();

        if (isset($outputData['data']) === true && $outputData['type'] !== 'redirect') {
            $outputData['data']['allowedTypes'] = $this->elasticSchemaTypes;
        }

        return $outputData;
    }
}