<?php

declare(strict_types=1);

/**
 * AbstractModelHandler.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\BridgeModel\BridgeFormModel;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Status;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Abstract action that provides model and form model management through a standardized pipeline.
 * Subclasses must implement getActionModels() and prepareOutputData().
 * The handle() method is NOT defined here - specialized subclasses (AbstractPageHandler, AbstractAjaxHandler) will define it.
 *
 * Pipeline: setupAction() -> setupMethod() -> handleMethod() -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractModelHandler extends AbstractBaseHandler
{
    /**
     * @var array<string, ActiveRecord> Loaded model instances indexed by name
     */
    protected array $models = [];

    /**
     * @var array<string, BridgeFormModel> Loaded form model instances indexed by name
     */
    protected array $formModels = [];

    public function __construct(
        LoggerInterface $logger,
        DboardConfig $dboardConfig,
        WebViewRenderer $viewRenderer,
        ResponseFactoryInterface $responseFactory,
        JsonResponseFactory $jsonResponseFactory,
        UrlGeneratorInterface $urlGenerator,
        Aliases $aliases,
        TranslatorInterface $translator,
        protected CurrentRoute $currentRoute,
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
        );
    }

    /**
     * Returns the action model configurations.
     * Each configuration defines how to load a model and its associated form model.
     *
     * @return array<string, ActionModel> Action model configurations indexed by name
     */
    abstract protected function getActionModels(): array;

    /**
     * Prepares the output data for rendering.
     * Must return an array with 'type' and corresponding data.
     *
     * @return array{type: string, view?: string, data: array<string, mixed>} Output configuration
     */
    abstract protected function prepareOutputData(): array;

    /**
     * Returns the primary key field names.
     * Override for composite or non-standard PKs.
     *
     * @return array<string> Field names (e.g., ['id'] or ['domain', 'name'])
     */
    protected function primaryKeys(): array
    {
        return ['id'];
    }

    /**
     * Extracts primary keys from the route.
     *
     * @return array<string, array<string, mixed>|null> Primary keys indexed by action model name
     */
    protected function extractPrimaryKeysFromRoute(): array
    {
        $keys = [];
        foreach ($this->primaryKeys() as $field) {
            $keys[$field] = $this->currentRoute->getArgument($field);
        }
        return ['main' => $keys];
    }

    /**
     * Extracts primary keys from a loaded model.
     *
     * @param string $name The model name
     * @return array<string, mixed> Route parameters
     */
    protected function extractPrimaryKeysFromModel(string $name = 'main'): array
    {
        return $this->models[$name]->primaryKeyValues();
    }

    /**
     * Sets up the action by loading all models and form models.
     * Returns a 404 response if a main model with a provided primary key is not found in the database.
     *
     * @return ResponseInterface|null Response if setup failed (e.g., 404), null if successful
     */
    protected function setupAction(): ?ResponseInterface
    {
        $actionModels = $this->getActionModels();
        $primaryKeys = $this->extractPrimaryKeysFromRoute();

        foreach ($actionModels as $name => $actionModel) {
            $pkey = $primaryKeys[$name] ?? null;

            // Load model if configured
            if ($actionModel->hasModel()) {
                $model = $actionModel->getModel($pkey);
                if ($model !== null) {
                    $this->models[$name] = $model;

                    // Return 404 if main model with pkey was not found (is new record)
                    if ($actionModel->isMain() && $pkey !== null && $model->isNew()) {
                        return $this->responseFactory->createResponse(Status::NOT_FOUND);
                    }
                }
            }

            // Load form model if configured
            if ($actionModel->hasFormModel()) {
                $model = $this->models[$name] ?? null;
                $formModel = $actionModel->getFormModel($model);
                if ($formModel !== null) {
                    $this->formModels[$name] = $formModel;
                }
            }
        }

        return null;
    }

    /**
     * Sets up the method-specific behavior.
     * For non-GET requests, loads form models from the request body.
     *
     * @return void
     */
    protected function setupMethod(): void
    {
        $bodyParams = $this->getBodyParams();
        if ($bodyParams !== null) {
            foreach ($this->formModels as $formModel) {
                $formModel->load($bodyParams);
            }
        }
    }

    /**
     * Handles the method-specific logic.
     * Override this method in subclasses to implement validation, saving, etc.
     *
     * @return void
     */
    protected function handleMethod(): void
    {
        // Default implementation is empty
        // Subclasses override this to implement their specific logic
    }

    /**
     * Dispatches the output based on the output data type.
     *
     * @param array{type: string, view?: string, data: array<string, mixed>} $outputData The output configuration
     * @return ResponseInterface The response
     */
    protected function output(array $outputData): ResponseInterface
    {
        return match ($outputData['type']) {
            'render' => $this->render($outputData['view'], $outputData['data']),
            'partial' => $this->renderPartial($outputData['view'], $outputData['data']),
            'json' => $this->renderJson($outputData['data']),
            'redirect' => $this->redirect($outputData['data']['route'], $outputData['data']['params'] ?? []),
        };
    }
}