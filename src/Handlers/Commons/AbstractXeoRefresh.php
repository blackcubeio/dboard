<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dcore\Models\Xeo;
use Blackcube\Dcore\Services\XeoRefreshService;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Handler\OutputType;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiLevel;
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
 * Abstract action for XEO refresh (regenerate xeo blocs from article blocs via SchemaSchema mapping).
 * POST-only. Deletes existing xeo blocs, then creates new ones from article schema + article blocs.
 *
 * Pipeline: setupAction() -> setupMethod() -> try { handleMethod() } catch -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractXeoRefresh extends AbstractAjaxHandler
{
    /**
     * Returns the model class name.
     *
     * @return string Fully qualified class name of the ActiveRecord model
     */
    abstract protected function getModelClass(): string;

    /**
     * Returns the entity name for display messages and file paths.
     *
     * @return string The entity name (e.g., 'content', 'tag')
     */
    abstract protected function getEntityName(): string;

    /**
     * Returns the file route prefix for file endpoints.
     *
     * @return string The route prefix (e.g., 'dboard.contents', 'dboard.tags')
     */
    abstract protected function getFileRoutePrefix(): string;

    /**
     * @var Slug|null The slug associated with the model
     */
    protected ?Slug $slug = null;

    /**
     * @var Xeo|null The Xeo record
     */
    protected ?Xeo $xeo = null;

    /**
     * @var bool Whether the refresh was successful
     */
    protected bool $refreshed = false;

    /**
     * Creates a new AbstractXeoRefresh instance.
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
        protected XeoRefreshService $xeoRefreshService,
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
     * Sets up the action: loads model, slug, and xeo.
     * Throws if xeo doesn't exist (must exist before refresh).
     *
     * @return ResponseInterface|null Response if setup failed, null if successful
     */
    protected function setupAction(): ?ResponseInterface
    {
        $setupResponse = parent::setupAction();
        if ($setupResponse === null) {
            $model = $this->models['main'];
            $entityName = $this->getEntityName();

            if ($model->getSlugId() === null) {
                throw new \RuntimeException(ucfirst($entityName) . ' must have a slug.');
            }

            $this->slug = Slug::query()
                ->andWhere([
                    'id' => $model->getSlugId(),
                ])
                ->one();

            if ($this->slug === null) {
                throw new \RuntimeException('Slug not found.');
            }

            $this->xeo = $this->slug->getXeoQuery()->one();
            if ($this->xeo === null) {
                throw new \RuntimeException('Xeo must exist before refreshing.');
            }
        }

        return $setupResponse;
    }

    /**
     * {@inheritdoc}
     */
    protected function setupMethod(): void
    {
    }

    /**
     * {@inheritdoc}
     *
     * 1. Delete existing xeo blocs (pivot + bloc + files)
     * 2. Article schema → SchemaSchema → create xeo blocs (order = 0 * 10000 + xeoSchemaId)
     * 3. Article blocs in order → SchemaSchema → create xeo blocs (order = position * 10000 + xeoSchemaId)
     */
    protected function handleMethod(): void
    {
        if ($this->request->getMethod() === Method::POST) {
            $this->xeoRefreshService->refresh($this->models['main'], $this->xeo);

            $this->refreshed = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        if ($this->refreshed === true) {
            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Close),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-common'),
                        $this->translator->translate('Structured data refreshed.', category: 'dboard-common'),
                        UiLevel::Success
                    ),
                ],
            ];
        } else {
            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Error', category: 'dboard-common'),
                        $this->translator->translate('Method not supported.', category: 'dboard-common'),
                        UiLevel::Danger
                    ),
                ],
            ];
        }

        return $outputData;
    }
}
