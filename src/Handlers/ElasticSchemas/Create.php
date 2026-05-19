<?php

declare(strict_types=1);

/**
 * Create.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\ElasticSchemas;

use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dcore\Models\Type;
use Blackcube\Dcore\Models\TypeElasticSchema;
use Blackcube\Dboard\Handlers\Commons\AbstractCreate;
use Blackcube\Dboard\Models\Forms\ElasticSchemaForm;
use Blackcube\Dboard\Models\Forms\ElasticSchemaTypeForm;
use Blackcube\BridgeModel\BridgeFormModel;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * ElasticSchema create action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Create extends AbstractCreate
{
    /** @var ElasticSchemaTypeForm[] */
    protected array $allowedTypes = [];

    protected function getModelClass(): string { return ElasticSchema::class; }
    protected function getFormModelClass(): string { return ElasticSchemaForm::class; }
    protected function getEntityName(): string { return 'elasticSchema'; }
    protected function getViewPrefix(): string { return 'ElasticSchemas'; }
    protected function getListRoute(): string { return 'dboard.elasticschemas'; }
    protected function getSuccessRoute(): string { return 'dboard.elasticschemas.edit'; }

    protected function setupAction(): ?ResponseInterface
    {
        $response = parent::setupAction();
        if ($response !== null) {
            return $response;
        }

        $typesQuery = Type::query()->orderBy(['name' => SORT_ASC]);
        $this->allowedTypes = [];

        foreach ($typesQuery->each() as $type) {
            $form = new ElasticSchemaTypeForm(translator: $this->translator);
            $form->setTypeId($type->getId());
            $form->setTypeName($type->getName());
            $form->setScenario('edit');
            $this->allowedTypes[$type->getId()] = $form;
        }

        if ($this->request->getMethod() === Method::POST) {
            BridgeFormModel::loadMultiple($this->allowedTypes, $this->getBodyParams());
        }

        return null;
    }

    protected function afterSave(bool $inTransaction): void
    {
        if (!$inTransaction) {
            return;
        }

        $elasticSchemaId = $this->models['main']->getId();
        foreach ($this->allowedTypes as $form) {
            if ($form->isAllowed()) {
                $typeElasticSchema = new TypeElasticSchema();
                $typeElasticSchema->setElasticSchemaId($elasticSchemaId);
                $typeElasticSchema->setTypeId((int) $form->getTypeId());
                $typeElasticSchema->save();
            }
        }
    }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();

        if (isset($outputData['data']) && $outputData['type'] !== 'redirect') {
            $outputData['data']['allowedTypes'] = $this->allowedTypes;
        }

        return $outputData;
    }
}