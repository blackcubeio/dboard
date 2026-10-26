<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dcore\Enums\ElasticSchemaKind;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Handler\OutputType;
use Blackcube\Dboard\Enums\TreePosition;
use Blackcube\ActiveRecord\Elastic\ElasticInterface;
use Blackcube\ActiveRecord\Hazeltree\HazeltreeInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * Abstract create action for Hazeltree/Elastic entities.
 * Inherits from AbstractPageHandler and uses ActionModel configuration.
 * Supports beforeSave() and afterSave() hooks for custom save logic.
 *
 * Pipeline: setupAction() -> setupMethod() -> handleMethod() -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractCreate extends AbstractPageHandler
{
    /**
     * Returns the model class name.
     *
     * @return string Fully qualified class name of the ActiveRecord model
     */
    abstract protected function getModelClass(): string;

    /**
     * Returns the form model class name.
     *
     * @return string Fully qualified class name of the BridgeFormModel
     */
    abstract protected function getFormModelClass(): string;

    /**
     * Returns the entity name for display and view data.
     *
     * @return string The entity name (e.g., 'content', 'tag')
     */
    abstract protected function getEntityName(): string;

    /**
     * Returns the view prefix for templates.
     *
     * @return string The view prefix (e.g., 'Contents', 'Tags')
     */
    abstract protected function getViewPrefix(): string;

    /**
     * Returns the route name for the list page (cancel button, breadcrumb).
     *
     * @return string The route name
     */
    abstract protected function getListRoute(): string;

    /**
     * Returns the route name for successful creation (typically edit page).
     *
     * @return string The route name
     */
    abstract protected function getSuccessRoute(): string;

    /**
     * Returns the view name for rendering.
     * If prefixed with /, root-relative (/ stripped). Otherwise, prefixed with getViewPrefix().
     *
     * @return string The view name
     */
    protected function getView(): string
    {
        return 'create';
    }

    /**
     * Returns the form scenario for creation.
     * Override this method to change the scenario.
     *
     * @return string The form scenario
     */
    protected function getFormScenario(): string
    {
        return 'create';
    }

    /**
     * Returns the maximum level for Hazeltree entities.
     * Must be implemented when the model supports HazeltreeInterface.
     *
     * @return int The maximum tree level
     * @throws \LogicException If not implemented for Hazeltree models
     */
    protected function getMaxLevel(): int
    {
        throw new \LogicException('getMaxLevel() must be implemented when model supports Hazeltree.');
    }

    /**
     * Returns the default move target for new Hazeltree nodes.
     * Override to customize positioning logic.
     *
     * @return array{targetId: int|null, mode: string}|null
     */
    protected function getDefaultMoveTarget(): ?array
    {
        return null;
    }

    /**
     * @var bool Whether the model implements HazeltreeInterface
     */
    protected bool $isHazeltree = false;

    /**
     * @var bool Whether the model implements ElasticInterface
     */
    protected bool $isElastic = false;

    /**
     * @var array<int, string> Elastic schema options for select (id => name)
     */
    protected array $elasticSchemaOptions = [];

    /**
     * @var ActiveQuery|null Query for target nodes (Hazeltree move)
     */
    protected ?ActiveQuery $targetQuery = null;

    /**
     * @var bool Flag indicating if save was successful (for redirect in prepareOutputData)
     */
    protected bool $saveSuccess = false;

    /**
     * {@inheritdoc}
     */
    protected function getActionModels(): array
    {
        return [
            'main' => new ActionModel(
                modelClass: $this->getModelClass(),
                formModelClass: $this->getFormModelClass(),
                formModelScenario: $this->getFormScenario(),
                isMain: false,
                translator: $this->translator,
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function extractPrimaryKeysFromRoute(): array
    {
        return [
            'main' => null,
        ];
    }

    /**
     * Sets up the action and prepares Hazeltree/Elastic specific data.
     *
     * @return ResponseInterface|null Response if setup failed, null if successful
     */
    protected function setupAction(): ?ResponseInterface
    {
        $setupResponse = parent::setupAction();
        if ($setupResponse === null) {
            $model = $this->models['main'];
            $this->isHazeltree = $model instanceof HazeltreeInterface;
            $this->isElastic = $model instanceof ElasticInterface;

            if ($this->isElastic === true) {
                $schemaQuery = ElasticSchema::query()
                    ->andWhere([
                        'hidden' => false,
                        'kind' => [
                            ElasticSchemaKind::Common->value,
                            ElasticSchemaKind::Page->value,
                        ],
                    ])
                    ->orderBy([
                        'name' => SORT_ASC,
                    ]);
                /** @var ElasticSchema $schema */
                foreach ($schemaQuery->each() as $schema) {
                    $this->elasticSchemaOptions[$schema->getId()] = $schema->getName();
                }
            }

            if ($this->isHazeltree === true) {
                $defaultTarget = $this->getDefaultMoveTarget();
                if ($defaultTarget !== null) {
                    $formModel = $this->formModels['main'];
                    $formModel->setMoveTargetId($defaultTarget['targetId']);
                    $formModel->setMoveMode($defaultTarget['mode']);
                }
                $modelClass = $this->getModelClass();
                $this->targetQuery = $modelClass::query()->natural();
            }
        }

        return $setupResponse;
    }

    /**
     * Hook called before validation.
     */
    protected function beforeValidate(): void
    {
    }

    /**
     * Hook called after validation (only if validation passed).
     */
    protected function afterValidate(): void
    {
    }

    /**
     * Hook called before save.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function beforeSave(bool $inTransaction): void
    {
    }

    /**
     * Hook called after save.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function afterSave(bool $inTransaction): void
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function handleMethod(): void
    {
        if ($this->request->getMethod() === Method::POST) {
            $formModel = $this->formModels['main'];

            $this->beforeValidate();
            if ($formModel->validate() === true) {
                $this->afterValidate();

                $model = $this->models['main'];
                $formModel->populateModel($model);

                $target = null;
                if ($this->isHazeltree === true && $formModel->isMove() === true && $formModel->getMoveTargetId() !== null) {
                    $modelClass = $this->getModelClass();
                    $target = $modelClass::query()
                        ->andWhere([
                            'id' => $formModel->getMoveTargetId(),
                        ])
                        ->one();
                }

                if ($target !== null) {
                    $treePosition = TreePosition::from($formModel->getMoveMode());
                    $wouldBeLevel = match ($treePosition) {
                        TreePosition::Into => $target->getLevel() + 1,
                        TreePosition::Before, TreePosition::After => $target->getLevel(),
                    };

                    if ($wouldBeLevel > $this->getMaxLevel()) {
                        $formModel->addError('Maximum level ' . $this->getMaxLevel() . ' exceeded.', [
                            'moveTargetId',
                        ]);
                    } else {
                        $this->beforeSave(false);
                        $transaction = $model->db()->beginTransaction();
                        try {
                            $this->beforeSave(true);
                            match ($treePosition) {
                                TreePosition::Before => $model->saveBefore($target),
                                TreePosition::Into => $model->saveInto($target),
                                TreePosition::After => $model->saveAfter($target),
                            };
                            $this->afterSave(true);
                            $transaction->commit();
                        } catch (\Throwable $e) {
                            $transaction->rollBack();
                            throw $e;
                        }
                        $this->afterSave(false);
                        $this->saveSuccess = true;
                    }
                } else {
                    $this->beforeSave(false);
                    $transaction = $model->db()->beginTransaction();
                    try {
                        $this->beforeSave(true);
                        $model->save();
                        $this->afterSave(true);
                        $transaction->commit();
                    } catch (\Throwable $e) {
                        $transaction->rollBack();
                        throw $e;
                    }
                    $this->afterSave(false);
                    $this->saveSuccess = true;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        if ($this->saveSuccess === true) {
            $outputData = [
                'type' => OutputType::Redirect->value,
                'data' => [
                    'route' => $this->getSuccessRoute(),
                    'params' => $this->extractPrimaryKeysFromModel(),
                ],
            ];
        } else {
            $view = $this->getView();
            $viewPath = str_starts_with($view, '/') === true ? substr($view, 1) : $this->getViewPrefix() . '/' . $view;

            $viewData = [
                $this->getEntityName() => $this->models['main'],
                'formModel' => $this->formModels['main'],
                'urlGenerator' => $this->urlGenerator,
                'currentRoute' => $this->currentRoute,
            ];

            if ($this->isHazeltree === true) {
                $viewData[$this->getEntityName() . 'Query'] = $this->targetQuery;
            }

            if ($this->isElastic === true) {
                $viewData['elasticSchemaOptions'] = $this->elasticSchemaOptions;
            }

            $outputData = [
                'type' => OutputType::Render->value,
                'view' => $viewPath,
                'data' => $viewData,
            ];
        }

        return $outputData;
    }
}