<?php

declare(strict_types=1);

/**
 * AbstractEdit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dcore\Enums\ElasticSchemaKind;
use Blackcube\Dcore\Models\Bloc;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Enums\ReorderMode;
use Blackcube\Dboard\Models\Forms\BlocForm;
use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Services\HazeltreeElasticService;
use Blackcube\ActiveRecord\Elastic\ElasticInterface;
use Blackcube\ActiveRecord\Hazeltree\HazeltreeInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Abstract edit action for Hazeltree/Elastic entities with blocs support.
 * Inherits from AbstractPageHandler and uses ActionModel configuration.
 * Supports beforeSave() and afterSave() hooks for custom save logic.
 *
 * Pipeline: setupAction() -> setupMethod() -> handleMethod() -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractEdit extends AbstractPageHandler
{
    /**
     * @var string|null Path to admin templates for blocs
     */
    protected ?string $adminTemplatesAlias = null;

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
     * Returns the route name for the list page (redirect after successful save).
     *
     * @return string The route name
     */
    abstract protected function getListRoute(): string;

    /**
     * Returns the view name for rendering.
     * If prefixed with /, root-relative (/ stripped). Otherwise, prefixed with getViewPrefix().
     *
     * @return string The view name
     */
    protected function getView(): string
    {
        return 'edit';
    }

    /**
     * Returns the view name for blocs partial rendering.
     * If prefixed with /, root-relative (/ stripped). Otherwise, prefixed with getViewPrefix().
     *
     * @return string The view name
     */
    protected function getBlocsView(): string
    {
        return '/Commons/_blocs';
    }

    /**
     * Returns whether to stay on the edit page after successful save.
     * Override this method to change the behavior.
     *
     * @return bool True to stay on page, false to redirect to list
     */
    protected function stayOnPageAfterSave(): bool
    {
        return false;
    }

    /**
     * Returns the form scenario for editing.
     * Override this method to change the scenario.
     *
     * @return string The form scenario
     */
    protected function getFormScenario(): string
    {
        return 'edit';
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
     * Returns the DOM element ID for the blocs list container.
     * Must be implemented when the model has blocs.
     *
     * @return string The DOM element ID
     * @throws \LogicException If not implemented for models with blocs
     */
    protected function getBlocsListId(): string
    {
        throw new \LogicException('getBlocsListId() must be implemented when model has blocs.');
    }

    /**
     * Returns the route name for bloc reorder action.
     * Must be implemented when the model has blocs.
     *
     * @return string The route name
     * @throws \LogicException If not implemented for models with blocs
     */
    protected function getBlocReorderRoute(): string
    {
        throw new \LogicException('getBlocReorderRoute() must be implemented when model has blocs.');
    }

    /**
     * Returns the route name for bloc add action.
     * Must be implemented when the model has blocs.
     *
     * @return string The route name
     * @throws \LogicException If not implemented for models with blocs
     */
    protected function getBlocAddRoute(): string
    {
        throw new \LogicException('getBlocAddRoute() must be implemented when model has blocs.');
    }

    /**
     * Returns the route name for bloc delete action.
     * Must be implemented when the model has blocs.
     *
     * @return string The route name
     * @throws \LogicException If not implemented for models with blocs
     */
    protected function getBlocDeleteRoute(): string
    {
        throw new \LogicException('getBlocDeleteRoute() must be implemented when model has blocs.');
    }

    /**
     * Returns the route parameter name for entity ID.
     * Override this method if the route uses a different parameter name (e.g., 'contentId').
     *
     * @return string The route parameter name
     */
    protected function getRouteIdParam(): string
    {
        return 'id';
    }

    /**
     * Returns the prefix for file storage.
     * Override this method to customize the prefix.
     *
     * @return string The file storage prefix (e.g., 'contents', 'tags')
     */
    protected function getFileStoragePrefix(): string
    {
        return strtolower($this->getEntityName() . 's');
    }

    /**
     * Returns the route prefix for file operations.
     * Override this method to customize the route prefix.
     *
     * @return string The route prefix (e.g., 'dboard.contents')
     */
    protected function getFileRoutePrefix(): string
    {
        return 'dboard.' . $this->getFileStoragePrefix();
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
     * @var bool Whether the model has blocs (has getBlocsQuery method)
     */
    protected bool $hasBlocs = false;

    /**
     * @var array<int, string> Elastic schema options for select (id => name)
     */
    protected array $elasticSchemaOptions = [];

    /**
     * @var ActiveQuery|null Query for target nodes (Hazeltree move)
     */
    protected ?ActiveQuery $targetQuery = null;

    /**
     * @var array<int, \Blackcube\Dcore\Models\Bloc> Bloc models indexed by ID
     */
    protected array $blocs = [];

    /**
     * @var array<int, BlocForm> Bloc form models indexed by bloc ID
     */
    protected array $blocForms = [];

    /**
     * @var array<int, array<string, mixed>> Initial file values for blocs
     */
    protected array $initialFileValues = [];

    /**
     * @var array<int, ElasticSchema> Allowed elastic schemas for the entity type
     */
    protected array $allowedElasticSchemas = [];

    /**
     * @var bool Flag indicating if save was successful (for redirect in prepareOutputData)
     */
    protected bool $saveSuccess = false;

    /**
     * @var array File endpoints for upload, preview, delete
     */
    protected array $fileEndpoints = [];

    /**
     * Creates a new AbstractEdit instance.
     *
     * @param WebViewRenderer $viewRenderer The view renderer
     * @param ResponseFactoryInterface $responseFactory The response factory
     * @param JsonResponseFactory $jsonResponseFactory The JSON response factory
     * @param UrlGeneratorInterface $urlGenerator The URL generator
     * @param Aliases $aliases The aliases service
     * @param HazeltreeElasticService $hazeltreeElasticService The Hazeltree/Elastic service
     * @param DboardConfig $dboardConfig The dboard configuration
     */
    public function __construct(
        LoggerInterface $logger,
        DboardConfig $dboardConfig,
        WebViewRenderer $viewRenderer,
        ResponseFactoryInterface $responseFactory,
        JsonResponseFactory $jsonResponseFactory,
        UrlGeneratorInterface $urlGenerator,
        Aliases $aliases,
        TranslatorInterface $translator,
        CurrentRoute $currentRoute,
        protected HazeltreeElasticService $hazeltreeElasticService,
    ) {
        parent::__construct(
            logger: $logger,
            dboardConfig: $dboardConfig,
            viewRenderer: $viewRenderer,
            responseFactory: $responseFactory,
            jsonResponseFactory: $jsonResponseFactory,
            urlGenerator: $urlGenerator,
            aliases: $aliases,
            translator: $translator,
            currentRoute: $currentRoute,
        );
        $this->adminTemplatesAlias = $dboardConfig->adminTemplatesAlias;
    }

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
                isMain: true, // Edit = existing model, 404 if not found
                translator: $this->translator,
            ),
        ];
    }

    /**
     * Sets up the action and prepares Hazeltree/Elastic/Blocs specific data.
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
        $this->hasBlocs = method_exists($model, 'getBlocsQuery');

        // Prepare elasticSchemaOptions if model implements ElasticInterface
        if ($this->isElastic) {
            foreach (ElasticSchema::query()->andWhere(['hidden' => false, 'kind' => [ElasticSchemaKind::Common->value, ElasticSchemaKind::Page->value]])->orderBy(['name' => SORT_ASC])->each() as $schema) {
                $this->elasticSchemaOptions[$schema->getId()] = $schema->getName();
            }
        }

        // Prepare Hazeltree target query (excluding self and descendants)
        if ($this->isHazeltree) {
            $modelClass = $this->getModelClass();
            $this->targetQuery = $modelClass::query()
                ->forNode($model)
                ->excludingSelf()
                ->excludingDescendants();
        }

        // Prepare blocs, blocForms, initialFileValues, and allowedElasticSchemas
        if ($this->hasBlocs) {
            foreach ($model->getBlocsQuery()->each() as $bloc) {
                $this->blocs[$bloc->getId()] = $bloc;
                $blocForm = BlocForm::createFromModel($bloc, $this->translator);
                $blocForm->setScenario('edit');
                $this->blocForms[$bloc->getId()] = $blocForm;
            }
            $this->initialFileValues = $this->hazeltreeElasticService->captureInitialFileValues($this->blocs);

            // Load allowedElasticSchemas via type
            $type = $model->getTypeQuery()->one();
            if ($type !== null) {
                foreach ($type->getElasticSchemasQuery()->andWhere(['hidden' => false])->each() as $schema) {
                    $this->allowedElasticSchemas[] = $schema;
                }
            }

            // Prepare file endpoints
            $fileRoutePrefix = $this->getFileRoutePrefix();
            $this->fileEndpoints = [
                'upload' => $this->urlGenerator->generate($fileRoutePrefix . '.files.upload'),
                'preview' => $this->urlGenerator->generate($fileRoutePrefix . '.files.preview'),
                'delete' => $this->urlGenerator->generate($fileRoutePrefix . '.files.delete'),
            ];
        }

        return null;
    }

    /**
     * Sets up the method-specific behavior.
     * Loads form models and bloc forms from the request body on POST.
     *
     * @return void
     */
    protected function setupMethod(): void
    {
        parent::setupMethod();

        if ($this->hasBlocs && $this->request->getMethod() === Method::POST) {
            BlocForm::loadMultiple($this->blocForms, $this->getBodyParams());
        }
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
     * Reloads bloc forms after successful save.
     */
    private function reloadBlocForms(): void
    {
        if ($this->hasBlocs) {
            foreach ($this->blocs as $bloc) {
                $bloc->refresh();
                $blocForm = BlocForm::createFromModel($bloc, $this->translator);
                $blocForm->setScenario('edit');
                $this->blocForms[$bloc->getId()] = $blocForm;
            }
        }
    }

    /**
     * Handles bloc actions (add, reorder) from AJAX POST (publishOnly trigger).
     * Detects the action from BlocForm params in the body, executes it,
     * then reloads blocs so prepareOutputData() renders the updated partial.
     */
    protected function handleBlocAction(): void
    {
        $bodyParams = $this->getBodyParams() ?? [];
        $blocParams = $bodyParams['BlocForm'] ?? [];
        $model = $this->models['main'];

        // Add bloc: BlocForm[elasticSchemaId] + BlocForm[blocAdd]
        $elasticSchemaId = $blocParams['elasticSchemaId'] ?? null;
        if ($elasticSchemaId !== null) {
            $schema = ElasticSchema::query()->andWhere(['id' => (int) $elasticSchemaId])->one();
            if ($schema !== null) {
                $bloc = new Bloc();
                $bloc->setElasticSchemaId($schema->getId());
                $afterBlocId = $blocParams['blocAdd'] ?? null;
                $position = 0;
                if ($afterBlocId !== null) {
                    $count = 0;
                    foreach ($model->getBlocsQuery()->each() as $existingBloc) {
                        $count++;
                        if ($existingBloc->getId() === (int) $afterBlocId) {
                            $position = $count + 1;
                            break;
                        }
                    }
                }
                $transaction = $model->db()->beginTransaction();
                try {
                    $bloc->save();
                    $model->attachBloc($bloc, $position);
                    $transaction->commit();
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
        }

        // Reorder: BlocForm[blocId] + BlocForm[mode]
        $reorderBlocId = $blocParams['blocId'] ?? null;
        $reorderMode = $blocParams['mode'] ?? null;
        if ($reorderBlocId !== null && $reorderMode !== null) {
            $bloc = Bloc::query()->andWhere(['id' => (int) $reorderBlocId])->one();
            if ($bloc !== null) {
                $transaction = $model->db()->beginTransaction();
                try {
                    if ($reorderMode === ReorderMode::MoveUp->value) {
                        $model->moveBlocUp($bloc);
                    } elseif ($reorderMode === ReorderMode::MoveDown->value) {
                        $model->moveBlocDown($bloc);
                    }
                    $transaction->commit();
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
        }

        // Reload blocs and forms from DB (new order, new bloc included)
        $model->refresh();
        $this->blocs = [];
        $this->blocForms = [];
        foreach ($model->getBlocsQuery()->each() as $bloc) {
            $this->blocs[$bloc->getId()] = $bloc;
            $blocForm = BlocForm::createFromModel($bloc, $this->translator);
            $blocForm->setScenario('edit');
            $this->blocForms[$bloc->getId()] = $blocForm;
        }

        // Re-apply posted form data to existing bloc forms
        BlocForm::loadMultiple($this->blocForms, $bodyParams);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleMethod(): void
    {
        if ($this->request->getMethod() !== Method::POST) {
            return;
        }

        // AJAX POST = bloc action (add/reorder) + refresh with form data preservation
        if ($this->isAjaxify() && $this->hasBlocs) {
            $this->handleBlocAction();
            return;
        }

        $formModel = $this->formModels['main'];
        $model = $this->models['main'];

        // Validate all forms
        $this->beforeValidate();
        $valid = $formModel->validate();
        if ($this->hasBlocs) {
            foreach ($this->blocForms as $blocForm) {
                if (!$blocForm->validate()) {
                    $valid = false;
                }
            }
        }

        if (!$valid) {
            return; // Validation errors, will re-render via prepareOutputData
        }
        $this->afterValidate();

        $formModel->populateModel($model);

        // Check move before transaction (calculate subtree depth)
        $target = null;
        if ($this->isHazeltree && $formModel->isMove() && $formModel->getMoveTargetId() !== null) {
            $modelClass = $this->getModelClass();
            $target = $modelClass::query()
                ->andWhere(['id' => $formModel->getMoveTargetId()])
                ->one();

            if ($target !== null) {
                // Calculate subtree depth
                $descendantsQuery = $model->relativeQuery()
                    ->children()
                    ->includeDescendants();
                $sourceLevel = $model->getLevel();
                $maxDescendantLevel = $sourceLevel;
                foreach ($descendantsQuery->each() as $descendant) {
                    if ($descendant->getLevel() > $maxDescendantLevel) {
                        $maxDescendantLevel = $descendant->getLevel();
                    }
                }
                $subtreeDepth = $maxDescendantLevel - $sourceLevel;

                $newSourceLevel = match ($formModel->getMoveMode()) {
                    'into' => $target->getLevel() + 1,
                    'before', 'after' => $target->getLevel(),
                    default => $target->getLevel(),
                };

                $wouldBeMaxLevel = $newSourceLevel + $subtreeDepth;

                if ($wouldBeMaxLevel > $this->getMaxLevel()) {
                    $formModel->addError('Maximum level ' . $this->getMaxLevel() . ' exceeded.', ['moveTargetId']);
                    return; // Error, will re-render via prepareOutputData
                }
            }
        }

        // Hazeltree move handling
        if ($this->isHazeltree && $target !== null) {
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

                // Save blocs if hasBlocs
                if ($this->hasBlocs) {
                    $this->hazeltreeElasticService->saveBlocs(
                        $this->blocForms,
                        $this->blocs,
                        $model,
                        $this->initialFileValues
                    );
                }

                $this->afterSave(true);
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
            $this->afterSave(false);
            $this->reloadBlocForms();
            $this->saveSuccess = true;
            return;
        }

        // Standard save (non-Hazeltree or no move)
        $this->beforeSave(false);
        $transaction = $model->db()->beginTransaction();
        try {
            $this->beforeSave(true);
            $model->save();

            // Save blocs if hasBlocs
            if ($this->hasBlocs) {
                $this->hazeltreeElasticService->saveBlocs(
                    $this->blocForms,
                    $this->blocs,
                    $model,
                    $this->initialFileValues
                );
            }

            $this->afterSave(true);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        $this->afterSave(false);
        $this->reloadBlocForms();
        $this->saveSuccess = true;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        // AJAX render (blocs list)
        if ($this->isAjax() && $this->hasBlocs) {
            $dndMode = ($this->request->getQueryParams()['dndMode'] ?? null) === '1';
            $view = $this->getBlocsView();
            $viewPath = str_starts_with($view, '/') ? substr($view, 1) : $this->getViewPrefix() . '/' . $view;

            return [
                'type' => OutputType::Partial->value,
                'view' => $viewPath,
                'data' => [
                    'urlGenerator' => $this->urlGenerator,
                    'model' => $this->models['main'],
                    'blocForms' => $this->blocForms,
                    'allowedElasticSchemas' => $this->allowedElasticSchemas,
                    'adminTemplatesAlias' => $this->adminTemplatesAlias,
                    'dndMode' => $dndMode,
                    'reorderRoute' => $this->getBlocReorderRoute(),
                    'addRoute' => $this->getBlocAddRoute(),
                    'deleteRoute' => $this->getBlocDeleteRoute(),
                    'fileEndpoints' => $this->fileEndpoints,
                    'blocsListId' => $this->getBlocsListId(),
                    'routeIdParam' => $this->getRouteIdParam(),
                ],
            ];
        }

        // Redirect after successful save (POST/Redirect/GET pattern)
        if ($this->saveSuccess) {
            if ($this->stayOnPageAfterSave()) {
                return [
                    'type' => OutputType::Redirect->value,
                    'data' => [
                        'route' => $this->currentRoute->getName(),
                        'params' => $this->currentRoute->getArguments(),
                    ],
                ];
            }
            return [
                'type' => OutputType::Redirect->value,
                'data' => [
                    'route' => $this->getListRoute(),
                    'params' => [],
                ],
            ];
        }

        // Render form (GET or POST with errors or stayOnPageAfterSave)
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

        if ($this->hasBlocs) {
            $viewData['blocForms'] = $this->blocForms;
            $viewData['allowedElasticSchemas'] = $this->allowedElasticSchemas;
            $viewData['adminTemplatesAlias'] = $this->adminTemplatesAlias;
            $viewData['reorderRoute'] = $this->getBlocReorderRoute();
            $viewData['addRoute'] = $this->getBlocAddRoute();
            $viewData['deleteRoute'] = $this->getBlocDeleteRoute();
            $viewData['fileEndpoints'] = $this->fileEndpoints;
            $viewData['blocsListId'] = $this->getBlocsListId();
            $viewData['routeIdParam'] = $this->getRouteIdParam();
        }

        return [
            'type' => OutputType::Render->value,
            'view' => $viewPath,
            'data' => $viewData,
        ];
    }
}