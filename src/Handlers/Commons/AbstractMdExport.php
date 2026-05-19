<?php

declare(strict_types=1);

/**
 * AbstractMdExport.php
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
use Blackcube\Dboard\Models\Forms\MdExportForm;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Abstract action for markdown export drawer.
 * GET: displays drawer with prompt textarea + download button + upload zone.
 * POST: generates markdown file and returns it for JS-based download.
 *
 * Pipeline: setupAction() -> setupMethod() -> handleMethod() -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractMdExport extends AbstractAjaxHandler
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
     * Returns the entity label for the warning message.
     * e.g. 'content', 'category', 'tag'
     */
    abstract protected function getEntityLabel(): string;

    /**
     * Returns the route name for the import action.
     */
    abstract protected function getImportRouteName(): string;

    /**
     * @var MdExportForm|null The form model for the prompt
     */
    protected ?MdExportForm $formModel = null;

    /**
     * @var string|null Generated markdown content (POST only)
     */
    protected ?string $markdownContent = null;

    /**
     * @var string|null Filename for download
     */
    protected ?string $downloadFilename = null;

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
            throw new \RuntimeException('The ' . $this->getEntityLabel() . ' must have a Type for markdown export.');
        }

        // Create form model
        $this->formModel = new MdExportForm(translator: $this->translator);

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
     * {@inheritdoc}
     */
    protected function handleMethod(): void
    {
        if ($this->request->getMethod() !== Method::POST) {
            return;
        }

        $model = $this->models['main'];
        $this->formModel->load($this->getBodyParams());
        $this->markdownContent = $this->elasticMdService->export($model, $this->formModel->getPrompt());

        // Build filename: entity-name-id.md
        $name = method_exists($model, 'getName') ? $model->getName() : '';
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($name));
        $slug = trim($slug, '-');
        if ($slug === '') {
            $slug = $this->getEntityName();
        }
        $this->downloadFilename = $slug . '-' . $model->getId() . '.md';
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        // POST: return markdown for download
        if ($this->markdownContent !== null) {
            return [
                'type' => 'markdown-download',
                'content' => $this->markdownContent,
                'filename' => $this->downloadFilename,
            ];
        }

        // GET: render drawer
        $model = $this->models['main'];

        $header = (string) $this->renderPartial('Commons/_drawer-header', [
            'title' => 'Export Markdown',
            'uiColor' => UiColor::Primary,
        ])->getBody();

        // Prepare file endpoints for upload widget
        $fileRoutePrefix = 'dboard.' . strtolower($this->getEntityName() . 's');
        $fileEndpoints = [
            'upload' => $this->urlGenerator->generate($fileRoutePrefix . '.files.upload'),
            'preview' => $this->urlGenerator->generate($fileRoutePrefix . '.files.preview'),
            'delete' => $this->urlGenerator->generate($fileRoutePrefix . '.files.delete'),
        ];

        $content = (string) $this->renderPartial('Commons/_md-export-content', [
            'model' => $model,
            'formModel' => $this->formModel,
            'entityLabel' => $this->getEntityLabel(),
            'formAction' => $this->urlGenerator->generate(
                $this->currentRoute->getName(),
                $this->extractPrimaryKeysFromModel()
            ),
            'importFormAction' => $this->urlGenerator->generate(
                $this->getImportRouteName(),
                $this->extractPrimaryKeysFromModel()
            ),
            'fileEndpoints' => $fileEndpoints,
        ])->getBody();

        return [
            'type' => OutputType::Json->value,
            'data' => [
                ...AureliaCommunication::dialog(DialogAction::Keep),
                ...AureliaCommunication::dialogContent($header, $content, UiColor::Primary),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function output(array $outputData): ResponseInterface
    {
        if (($outputData['type'] ?? null) === 'markdown-download') {
            return $this->downloadContent(
                $outputData['content'],
                $outputData['filename'],
                ['mimeType' => 'text/markdown; charset=utf-8']
            );
        }

        return parent::output($outputData);
    }
}
