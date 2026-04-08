<?php

declare(strict_types=1);

/**
 * AbstractCreate.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dcore\Enums\ElasticSchemaKind;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
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
 * @copyright 2010-2026 Blackcube
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
                isMain: false, // Create = new record, no 404 if not found
                translator: $this->translator,
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function extractPrimaryKeysFromRoute(): array
    {
        return ['main' => null]; // No primary key for creation
    }

    /**
     * Sets up the action and prepares Hazeltree/Elastic specific data.
     *
     * @return ResponseInterface|null Response if setup failed, null if successful
     */
    protected function setupAction(): ?ResponseInterface
    {
        $response = parent::setupAction();
        if ($response !== null) {
            return $response;
        }

        $model = $this->models['main'];
        $this->isHazeltree = $model instanceof HazeltreeInterface;
        $this->isElastic = $model instanceof ElasticInterface;

        // Prepare elasticSchemaOptions if model implements ElasticInterface
        if ($this->isElastic) {
            $schemaQuery = ElasticSchema::query()
                ->andWhere(['hidden' => false, 'kind' => [ElasticSchemaKind::Common->value, ElasticSchemaKind::Page->value]])
                ->orderBy(['name' => SORT_ASC]);
            foreach ($schemaQuery->each() as $schema) {
                $this->elasticSchemaOptions[$schema->getId()] = $schema->getName();
            }
        }

        // Prepare Hazeltree defaults and target query
        if ($this->isHazeltree) {
            $defaultTarget = $this->getDefaultMoveTarget();
            if ($defaultTarget !== null) {
                $formModel = $this->formModels['main'];
                $formModel->setMoveTargetId($defaultTarget['targetId']);
                $formModel->setMoveMode($defaultTarget['mode']);
            }
            $modelClass = $this->getModelClass();
            $this->targetQuery = $modelClass::query()->natural();
        }

        return null;
    }

    /**
     * Hook called before validation.
     */
    protected function beforeValidate(): void
    {
        // Hook for subclasses
    }

    /**
     * Hook called after validation (only if validation passed).
     */
    protected function afterValidate(): void
    {
        // Hook for subclasses
    }

    /**
     * Hook called before save.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function beforeSave(bool $inTransaction): void
    {
        // Hook for subclasses
    }

    /**
     * Hook called after save.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function afterSave(bool $inTransaction): void
    {
        // Hook for subclasses
    }

    /**
     * {@inheritdoc}
     */
    protected function handleMethod(): void
    {
        if ($this->request->getMethod() !== Method::POST) {
            return;
        }

        $formModel = $this->formModels['main'];

        $this->beforeValidate();
        if (!$formModel->validate()) {
            return; // Validation errors, will re-render via prepareOutputData
        }
        $this->afterValidate();

        $model = $this->models['main'];
        $formModel->populateModel($model);

        // Hazeltree move handling
        if ($this->isHazeltree && $formModel->isMove() && $formModel->getMoveTargetId() !== null) {
            $modelClass = $this->getModelClass();
            $target = $modelClass::query()
                ->andWhere(['id' => $formModel->getMoveTargetId()])
                ->one();

            if ($target !== null) {
                $wouldBeLevel = match ($formModel->getMoveMode()) {
                    'into' => $target->getLevel() + 1,
                    'before', 'after' => $target->getLevel(),
                    default => $target->getLevel(),
                };

                if ($wouldBeLevel > $this->getMaxLevel()) {
                    $formModel->addError('Maximum level ' . $this->getMaxLevel() . ' exceeded.', ['moveTargetId']);
                    return; // Error, will re-render via prepareOutputData
                }

                $this->beforeSave(false);
                $transaction = $model->db()->beginTransaction();
                try {
                    $this->beforeSave(true);
                    match ($formModel->getMoveMode()) {
                        'into' => $model->saveInto($target),
                        'before' => $model->saveBefore($target),
                        'after' => $model->saveAfter($target),
                        default => $model->save(),
                    };
                    $this->afterSave(true);
                    $transaction->commit();
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
                $this->afterSave(false);
                $this->saveSuccess = true;
                return;
            }
        }

        // Standard save (non-Hazeltree or no move)
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

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        // Redirect after successful save
        if ($this->saveSuccess) {
            return [
                'type' => OutputType::Redirect->value,
                'data' => [
                    'route' => $this->getSuccessRoute(),
                    'params' => $this->extractPrimaryKeysFromModel(),
                ],
            ];
        }

        // Render form (GET or POST with errors)
        $view = $this->getView();
        $viewPath = str_starts_with($view, '/') ? substr($view, 1) : $this->getViewPrefix() . '/' . $view;

        $viewData = [
            $this->getEntityName() => $this->models['main'],
            'formModel' => $this->formModels['main'],
            'urlGenerator' => $this->urlGenerator,
            'currentRoute' => $this->currentRoute,
        ];

        if ($this->isHazeltree) {
            $viewData[$this->getEntityName() . 'Query'] = $this->targetQuery;
        }

        if ($this->isElastic) {
            $viewData['elasticSchemaOptions'] = $this->elasticSchemaOptions;
        }

        return [
            'type' => OutputType::Render->value,
            'view' => $viewPath,
            'data' => $viewData,
        ];
    }
}