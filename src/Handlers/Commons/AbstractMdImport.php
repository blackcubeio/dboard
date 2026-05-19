<?php

declare(strict_types=1);

/**
 * AbstractMdImport.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dcore\Services\ElasticMdService;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Blackcube\Dcore\Services\FileService;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Abstract action for markdown import.
 * POST only: receives a @bltmp file path, reads the markdown, imports it into the model, returns a partial HTML response.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractMdImport extends AbstractAjaxHandler
{
    /**
     * Returns the model class name.
     */
    abstract protected function getModelClass(): string;

    /**
     * Returns the entity name for display.
     */
    abstract protected function getEntityName(): string;

    /**
     * Returns the entity label for the messages.
     */
    abstract protected function getEntityLabel(): string;

    /**
     * @var array|null Import result from the service
     */
    protected ?array $importResult = null;

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
        protected ElasticMdService $elasticMdService,
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
                formModelClass: null,
                isMain: true,
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function setupAction(): ?ResponseInterface
    {
        $response = parent::setupAction();
        if ($response !== null) {
            return $response;
        }

        // Verify model has a Type
        $model = $this->models['main'];
        if ($model->getTypeId() === null) {
            throw new \RuntimeException('The ' . $this->getEntityLabel() . ' must have a Type for markdown import.');
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function setupMethod(): void
    {
        // No form model to load
    }

    /**
     * {@inheritdoc}
     */
    protected function handleMethod(): void
    {
        $model = $this->models['main'];

        // Get @bltmp file path from form body
        $bodyParams = $this->getBodyParams() ?? [];
        $mdFilePath = $bodyParams['mdFile'] ?? null;

        if ($mdFilePath === null || $mdFilePath === '') {
            $this->importResult = [
                'success' => false,
                'errors' => ['No file received.'],
                'warnings' => [],
                'blocsCreated' => 0,
            ];
            return;
        }

        // Validate the file is in @bltmp
        if (!$this->fileService->isTmpPath($mdFilePath)) {
            $this->importResult = [
                'success' => false,
                'errors' => ['Invalid file path.'],
                'warnings' => [],
                'blocsCreated' => 0,
            ];
            return;
        }

        // Check file exists
        if (!$this->fileService->fileExists($mdFilePath)) {
            $this->importResult = [
                'success' => false,
                'errors' => ['Temporary file does not exist or has expired.'],
                'warnings' => [],
                'blocsCreated' => 0,
            ];
            return;
        }

        // Read file content
        $markdown = $this->fileService->read($mdFilePath);

        // Process import
        $this->importResult = $this->elasticMdService->import($model, $markdown);

        // Delete temp file
        $this->fileService->delete($mdFilePath);
    }

    /**
     * Returns the file route prefix for file endpoints.
     */
    protected function getFileRoutePrefix(): string
    {
        return 'dboard.' . strtolower($this->getEntityName() . 's');
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $fileRoutePrefix = $this->getFileRoutePrefix();
        $fileEndpoints = [
            'upload' => $this->urlGenerator->generate($fileRoutePrefix . '.files.upload'),
            'preview' => $this->urlGenerator->generate($fileRoutePrefix . '.files.preview'),
            'delete' => $this->urlGenerator->generate($fileRoutePrefix . '.files.delete'),
        ];

        if ($this->importResult !== null && $this->importResult['success']) {
            $blocsCreated = (int) $this->importResult['blocsCreated'];
            return [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::RefreshAndClose),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Import successful', category: 'dboard-common'),
                        $this->translator->translate('{count} block(s) created.', ['count' => $blocsCreated], 'dboard-common'),
                        UiColor::Success
                    ),
                ],
            ];
        }

        return [
            'type' => OutputType::Partial->value,
            'view' => 'Commons/_md-import-form-content',
            'data' => [
                'importResult' => $this->importResult,
                'fileEndpoints' => $fileEndpoints,
                'importFormAction' => $this->urlGenerator->generate(
                    $this->currentRoute->getName(),
                    $this->extractPrimaryKeysFromModel()
                ),
            ],
        ];
    }
}
