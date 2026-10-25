<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Types;

use Blackcube\Dcore\Enums\ElasticSchemaKind;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dcore\Models\Type;
use Blackcube\Dcore\Models\TypeElasticSchema;
use Blackcube\Dboard\Handlers\Commons\AbstractCreate;
use Blackcube\Dboard\Models\Forms\TypeElasticSchemaForm;
use Blackcube\Dboard\Models\Forms\TypeForm;
use Blackcube\BridgeFormModel\BridgeFormModel;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * Type create action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Create extends AbstractCreate
{
    /** @var TypeElasticSchemaForm[] */
    protected array $allowedElasticSchemas = [];

    protected function getModelClass(): string { return Type::class; }
    protected function getFormModelClass(): string { return TypeForm::class; }
    protected function getEntityName(): string { return 'type'; }
    protected function getViewPrefix(): string { return 'Types'; }
    protected function getListRoute(): string { return 'dboard.types'; }
    protected function getSuccessRoute(): string { return 'dboard.types.edit'; }

    protected function setupAction(): ?ResponseInterface
    {
        $setupResponse = parent::setupAction();
        if ($setupResponse === null) {
            $elasticSchemasQuery = ElasticSchema::query()->andWhere([
                'hidden' => false,
                'kind' => [
                    ElasticSchemaKind::Common->value,
                    ElasticSchemaKind::Bloc->value,
                ],
            ])->orderBy([
                'name' => SORT_ASC,
            ]);
            $this->allowedElasticSchemas = [];

            /** @var ElasticSchema $elasticSchema */
            foreach ($elasticSchemasQuery->each() as $elasticSchema) {
                $form = new TypeElasticSchemaForm(translator: $this->translator);
                $form->setElasticSchemaId($elasticSchema->getId());
                $form->setElasticSchemaName($elasticSchema->getName());
                $form->setScenario('edit');
                $this->allowedElasticSchemas[$elasticSchema->getId()] = $form;
            }

            if ($this->request->getMethod() === Method::POST) {
                BridgeFormModel::loadMultiple($this->allowedElasticSchemas, $this->getBodyParams());
            }
        }

        return $setupResponse;
    }

    protected function afterSave(bool $inTransaction): void
    {
        if ($inTransaction === true) {
            $typeId = $this->models['main']->getId();
            foreach ($this->allowedElasticSchemas as $form) {
                if ($form->isAllowed() === true) {
                    $typeElasticSchema = new TypeElasticSchema();
                    $typeElasticSchema->setTypeId($typeId);
                    $typeElasticSchema->setElasticSchemaId((int) $form->getElasticSchemaId());
                    $typeElasticSchema->save();
                }
            }
        }
    }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();

        if (isset($outputData['data']) === true && $outputData['type'] !== 'redirect') {
            $outputData['data']['allowedElasticSchemas'] = $this->allowedElasticSchemas;
        }

        return $outputData;
    }
}