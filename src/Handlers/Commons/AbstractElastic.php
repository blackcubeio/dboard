<?php

declare(strict_types=1);

/**
 * AbstractElastic.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dcore\Services\FileService;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Blackcube\BridgeModel\BridgeFormModel;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Abstract action for elastic properties drawer.
 * Inherits from AbstractAjaxHandler and uses ActionModel configuration.
 *
 * Pipeline: setupAction() -> setupMethod() -> try { handleMethod() } catch -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractElastic extends AbstractAjaxHandler
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
     * Returns the entity name for display messages and file paths.
     *
     * @return string The entity name (e.g., 'content', 'tag')
     */
    abstract protected function getEntityName(): string;

    /**
     * Returns the file route prefix for file endpoints.
     *
     * @return string The route prefix (e.g., 'contents', 'tags')
     */
    abstract protected function getFileRoutePrefix(): string;

    /**
     * @var BridgeFormModel|null The form model for elastic properties
     */
    protected ?BridgeFormModel $formModel = null;

    /**
     * @var ElasticSchema|null The elastic schema for the model
     */
    protected ?ElasticSchema $elasticSchema = null;

    /**
     * @var bool Whether the save operation was successful
     */
    protected bool $saved = false;

    /**
     * Creates a new AbstractElastic instance.
     *
     * @param WebViewRenderer $viewRenderer The view renderer
     * @param ResponseFactoryInterface $responseFactory The response factory
     * @param JsonResponseFactory $jsonResponseFactory The JSON response factory
     * @param UrlGeneratorInterface $urlGenerator The URL generator
     * @param Aliases $aliases The aliases service
     * @param FileService $fileService The file save service
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
        protected FileService $fileService,
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
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionModels(): array
    {
        return [
            'main' => new ActionModel(
                modelClass: $this->getModelClass(),
                formModelClass: null, // Form created manually because we need createFromModel
                isMain: true, // 404 if not found
            ),
        ];
    }

    /**
     * Sets up the action and prepares the form model.
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

        // Check elastic schema
        if ($model->getElasticSchemaId() === null) {
            throw new \RuntimeException('No property schema defined.');
        }

        // Create form model from active record
        $formModelClass = $this->getFormModelClass();
        $this->formModel = $formModelClass::createFromModel($model, $this->translator);
        $this->formModel->setScenario('elastic');

        // Load elastic schema
        $this->elasticSchema = ElasticSchema::query()
            ->andWhere(['id' => $model->getElasticSchemaId()])
            ->one();

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function setupMethod(): void
    {
        if ($this->request->getMethod() === Method::POST) {
            $this->formModel->load($this->getBodyParams());
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
     * {@inheritdoc}
     */
    protected function handleMethod(): void
    {
        if ($this->request->getMethod() !== Method::POST) {
            return;
        }

        $this->beforeValidate();
        if (!$this->formModel->validate()) {
            return; // Form will be re-displayed with errors
        }
        $this->afterValidate();

        $model = $this->models['main'];
        $this->formModel->populateModel($model);

        $this->beforeSave(false);
        $transaction = $model->db()->beginTransaction();
        try {
            $this->beforeSave(true);
            $model->save();

            // Process files: move @bltmp/ -> @blfs/{entityPath}/
            $this->fileService->processEntityFiles($model);

            $this->afterSave(true);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        $this->afterSave(false);

        $this->saved = true;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        // POST success
        if ($this->saved) {
            return [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Close),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-common'),
                        $this->translator->translate('Properties saved.', category: 'dboard-common'),
                        UiColor::Success
                    ),
                ],
            ];
        }

        // GET or invalid POST - display form
        $model = $this->models['main'];
        $fileRoutePrefix = $this->getFileRoutePrefix();

        $fileEndpoints = [
            'upload' => $this->urlGenerator->generate($fileRoutePrefix . '.files.upload'),
            'preview' => $this->urlGenerator->generate($fileRoutePrefix . '.files.preview'),
            'delete' => $this->urlGenerator->generate($fileRoutePrefix . '.files.delete'),
        ];

        $elasticSchemaName = $this->elasticSchema?->getName() ?? $this->translator->translate('Properties', category: 'dboard-common');

        $header = (string) $this->renderPartial('Commons/_drawer-header', [
            'title' => $this->translator->translate('Properties', category: 'dboard-common'),
            'uiColor' => UiColor::Primary,
        ])->getBody();

        $content = (string) $this->renderPartial('Commons/_elastic-content', [
            'model' => $model,
            'formModel' => $this->formModel,
            'urlGenerator' => $this->urlGenerator,
            'fileEndpoints' => $fileEndpoints,
            'elasticSchemaName' => $elasticSchemaName,
            'elasticSchema' => $this->elasticSchema,
            'formAction' => $this->urlGenerator->generate(
                $this->currentRoute->getName(),
                $this->extractPrimaryKeysFromModel()
            ),
        ])->getBody();

        return [
            'type' => OutputType::Json->value,
            'data' => [
                ...AureliaCommunication::dialog(DialogAction::Keep),
                ...AureliaCommunication::dialogContent($header, $content, UiColor::Primary),
            ],
        ];
    }
}