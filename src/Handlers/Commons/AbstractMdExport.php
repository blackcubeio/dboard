<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dcore\Services\ElasticMdService;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Handler\OutputType;
use Blackcube\Dboard\Models\Forms\MdExportForm;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
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
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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
        $setupResponse = parent::setupAction();
        if ($setupResponse === null) {
            $model = $this->models['main'];
            if ($model->getTypeId() === null) {
                throw new \RuntimeException('The ' . $this->getEntityLabel() . ' must have a Type for markdown export.');
            }

            $this->formModel = new MdExportForm(translator: $this->translator);
        }

        return $setupResponse;
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
        if ($this->request->getMethod() === Method::POST) {
            $model = $this->models['main'];
            $this->formModel->load($this->getBodyParams());
            $this->markdownContent = $this->elasticMdService->export($model, $this->formModel->getPrompt());

            $name = method_exists($model, 'getName') === true ? $model->getName() : '';
            $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($name));
            $slug = trim($slug, '-');
            if ($slug === '') {
                $slug = $this->getEntityName();
            }
            $this->downloadFilename = $slug . '-' . $model->getId() . '.md';
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        if ($this->markdownContent !== null) {
            $outputData = [
                'type' => 'markdown-download',
                'content' => $this->markdownContent,
                'filename' => $this->downloadFilename,
            ];
        } else {
            $model = $this->models['main'];

            $fileRoutePrefix = 'dboard.' . strtolower($this->getEntityName() . 's');
            $fileEndpoints = [
                'upload' => $this->urlGenerator->generate($fileRoutePrefix . '.files.upload'),
                'preview' => $this->urlGenerator->generate($fileRoutePrefix . '.files.preview'),
                'delete' => $this->urlGenerator->generate($fileRoutePrefix . '.files.delete'),
            ];

            $content = (string) $this->renderPartial('Commons/_md-export-content', [
                'model' => $model,
                'formModel' => $this->formModel,
                'entityName' => $this->getEntityName(),
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

            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Keep),
                    ...AureliaCommunication::dialogContent(Bleet::dialogFrame(DialogType::Drawer)
                        ->title('Export Markdown')
                        ->primary()
                        ->content($content)
                        ->render()),
                ],
            ];
        }

        return $outputData;
    }

    /**
     * {@inheritdoc}
     */
    protected function output(array $outputData): ResponseInterface
    {
        if (($outputData['type'] ?? null) === 'markdown-download') {
            $exportResponse = $this->downloadContent(
                $outputData['content'],
                $outputData['filename'],
                [
                    'mimeType' => 'text/markdown; charset=utf-8',
                ]
            );
        } else {
            $exportResponse = parent::output($outputData);
        }

        return $exportResponse;
    }
}
