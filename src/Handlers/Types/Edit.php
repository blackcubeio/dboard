<?php

declare(strict_types=1);

/**
 * Edit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Types;

use Blackcube\Dcore\Enums\ElasticSchemaKind;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dcore\Models\Type;
use Blackcube\Dcore\Models\TypeElasticSchema;
use Blackcube\Dboard\Handlers\Commons\AbstractEdit;
use Blackcube\Dboard\Models\Forms\TypeElasticSchemaForm;
use Blackcube\Dboard\Models\Forms\TypeForm;
use Blackcube\BridgeModel\BridgeFormModel;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * Type edit action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Edit extends AbstractEdit
{
    /** @var TypeElasticSchemaForm[] */
    protected array $typeElasticSchemas = [];

    protected function getModelClass(): string { return Type::class; }
    protected function getFormModelClass(): string { return TypeForm::class; }
    protected function getEntityName(): string { return 'type'; }
    protected function getViewPrefix(): string { return 'Types'; }
    protected function getListRoute(): string { return 'dboard.types'; }

    protected function setupAction(): ?ResponseInterface
    {
        $response = parent::setupAction();
        if ($response !== null) {
            return $response;
        }

        $typeId = $this->models['main']->getId();

        $elasticSchemasQuery = ElasticSchema::query()
            ->andWhere(['hidden' => false, 'kind' => [ElasticSchemaKind::Common->value, ElasticSchemaKind::Bloc->value]])
            ->orderBy(['name' => SORT_ASC]);
        $this->typeElasticSchemas = [];

        foreach ($elasticSchemasQuery->each() as $elasticSchema) {
            $typeElasticSchema = TypeElasticSchema::query()
                ->andWhere(['typeId' => $typeId, 'elasticSchemaId' => $elasticSchema->getId()])
                ->one();

            $form = new TypeElasticSchemaForm(translator: $this->translator);
            if ($typeElasticSchema !== null) {
                $form->initFromModel($typeElasticSchema);
                $form->setAllowed(true);
            }
            $form->setTypeId($typeId);
            $form->setElasticSchemaId($elasticSchema->getId());
            $form->setElasticSchemaName($elasticSchema->getName());
            $form->setScenario('edit');
            $this->typeElasticSchemas[$elasticSchema->getId()] = $form;
        }

        if ($this->request->getMethod() === Method::POST) {
            BridgeFormModel::loadMultiple($this->typeElasticSchemas, $this->getBodyParams());
        }

        return null;
    }

    protected function afterSave(bool $inTransaction): void
    {
        if (!$inTransaction) {
            return;
        }

        $typeId = $this->models['main']->getId();
        foreach ($this->typeElasticSchemas as $form) {
            $elasticSchemaId = (int) $form->getElasticSchemaId();
            $allowed = $form->isAllowed();

            $typeElasticSchema = TypeElasticSchema::query()
                ->andWhere(['typeId' => $typeId, 'elasticSchemaId' => $elasticSchemaId])
                ->one();

            if ($allowed && $typeElasticSchema === null) {
                $typeElasticSchema = new TypeElasticSchema();
                $typeElasticSchema->setTypeId($typeId);
                $typeElasticSchema->setElasticSchemaId($elasticSchemaId);
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
            $outputData['data']['allowedElasticSchemas'] = $this->typeElasticSchemas;
        }

        return $outputData;
    }
}