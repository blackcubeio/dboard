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
use Blackcube\Dboard\Handlers\Commons\AbstractCreate;
use Blackcube\Dboard\Models\Forms\ElasticSchemaForm;
use Blackcube\Dboard\Models\Forms\ElasticSchemaTypeForm;
use Blackcube\BridgeFormModel\BridgeFormModel;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * ElasticSchema create action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Create extends AbstractCreate
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
        $setupResponse = parent::setupAction();
        if ($setupResponse === null) {
            $typesQuery = Type::query()->orderBy([
                'name' => SORT_ASC,
            ]);
            $this->allowedTypes = [];

            /** @var Type $type */
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
        }

        return $setupResponse;
    }

    protected function afterSave(bool $inTransaction): void
    {
        if ($inTransaction === true) {
            $elasticSchemaId = $this->models['main']->getId();
            foreach ($this->allowedTypes as $form) {
                if ($form->isAllowed() === true) {
                    $typeElasticSchema = new TypeElasticSchema();
                    $typeElasticSchema->setElasticSchemaId($elasticSchemaId);
                    $typeElasticSchema->setTypeId((int) $form->getTypeId());
                    $typeElasticSchema->save();
                }
            }
        }
    }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();

        if (isset($outputData['data']) === true && $outputData['type'] !== 'redirect') {
            $outputData['data']['allowedTypes'] = $this->allowedTypes;
        }

        return $outputData;
    }
}