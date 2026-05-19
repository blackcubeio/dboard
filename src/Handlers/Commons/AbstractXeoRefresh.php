<?php

declare(strict_types=1);

/**
 * AbstractXeoRefresh.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dcore\Models\Bloc;
use Blackcube\Dcore\Models\SchemaSchema;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dcore\Models\Xeo;
use Blackcube\Dcore\Models\XeoBloc;
use Blackcube\Dcore\Services\FileService;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
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
 * Abstract action for XEO refresh (regenerate xeo blocs from article blocs via SchemaSchema mapping).
 * POST-only. Deletes existing xeo blocs, then creates new ones from article schema + article blocs.
 *
 * Pipeline: setupAction() -> setupMethod() -> try { handleMethod() } catch -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube
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
     * Returns the article bloc pivot class name.
     *
     * @return string Fully qualified class name (e.g., ContentBloc::class, TagBloc::class)
     */
    abstract protected function getArticleBlocPivotClass(): string;

    /**
     * Returns the FK column name in the article bloc pivot table.
     *
     * @return string The FK column (e.g., 'contentId', 'tagId')
     */
    abstract protected function getArticleBlocFkColumn(): string;

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
     * Sets up the action: loads model, slug, and xeo.
     * Throws if xeo doesn't exist (must exist before refresh).
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
        $entityName = $this->getEntityName();

        if ($model->getSlugId() === null) {
            throw new \RuntimeException(ucfirst($entityName) . ' must have a slug.');
        }

        $this->slug = Slug::query()
            ->andWhere(['id' => $model->getSlugId()])
            ->one();

        if ($this->slug === null) {
            throw new \RuntimeException('Slug not found.');
        }

        $this->xeo = $this->slug->getXeoQuery()->one();
        if ($this->xeo === null) {
            throw new \RuntimeException('Xeo must exist before refreshing.');
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function setupMethod(): void
    {
        // POST-only, no form to load
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
        if ($this->request->getMethod() !== Method::POST) {
            return;
        }

        $model = $this->models['main'];
        $entityName = $this->getEntityName();

        $transaction = $this->xeo->db()->beginTransaction();
        try {
            // 1. Delete existing xeo blocs
            foreach ($this->xeo->getXeoBlocsQuery()->each() as $xeoBlocPivot) {
                $bloc = $xeoBlocPivot->getBlocQuery()->one();
                $xeoBlocPivot->delete();
                if ($bloc !== null) {
                    $this->fileService->deleteBlocFiles($bloc, $model);
                    $bloc->delete();
                }
            }

            // 2. Article schema → SchemaSchema → xeo blocs
            $articleSchemaId = $model->getElasticSchemaId();
            if ($articleSchemaId !== null) {
                $schemaSchemas = SchemaSchema::query()
                    ->andWhere(['regularElasticSchemaId' => $articleSchemaId])
                    ->all();
                foreach ($schemaSchemas as $schemaSchema) {
                    $order = 0 * 10000 + $schemaSchema->getXeoElasticSchemaId();
                    $this->createXeoBloc($schemaSchema, $model, $order);
                }
            }

            // 3. Article blocs → SchemaSchema → xeo blocs
            $pivotClass = $this->getArticleBlocPivotClass();
            $fkColumn = $this->getArticleBlocFkColumn();
            $articleBlocPivots = $pivotClass::query()
                ->andWhere([$fkColumn => $model->getId()])
                ->orderBy(['order' => SORT_ASC])
                ->all();

            foreach ($articleBlocPivots as $pivot) {
                $bloc = $pivot->getBlocQuery()->one();
                if ($bloc === null) {
                    continue;
                }
                $blocSchemaId = $bloc->getElasticSchemaId();
                if ($blocSchemaId === null) {
                    continue;
                }
                $schemaSchemas = SchemaSchema::query()
                    ->andWhere(['regularElasticSchemaId' => $blocSchemaId])
                    ->all();
                $position = $pivot->getOrder();
                foreach ($schemaSchemas as $schemaSchema) {
                    $order = $position * 10000 + $schemaSchema->getXeoElasticSchemaId();
                    $this->createXeoBloc($schemaSchema, $bloc, $order);
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->refreshed = true;
    }

    /**
     * Creates a xeo bloc from a SchemaSchema mapping.
     *
     * @param SchemaSchema $schemaSchema The schema mapping
     * @param object $source The source object (Content/Tag or Bloc) for prefill
     * @param int $order The order value for the XeoBloc pivot
     */
    private function createXeoBloc(SchemaSchema $schemaSchema, object $source, int $order): void
    {
        $model = $this->models['main'];
        $entityName = $this->getEntityName();

        $xeoBloc = new Bloc();
        $xeoBloc->setElasticSchemaId($schemaSchema->getXeoElasticSchemaId());
        $xeoBloc->setActive(false);

        // Prefill via mapping
        $mappingJson = $schemaSchema->getMapping();
        if ($mappingJson !== null) {
            $decoded = json_decode($mappingJson, true);
            if (isset($decoded['mapping']) && is_array($decoded['mapping'])) {
                foreach ($decoded['mapping'] as $sourceField => $targetField) {
                    $sourceValue = $source->$sourceField ?? null;
                    if ($sourceValue !== null && $sourceValue !== '') {
                        $xeoBloc->$targetField = $sourceValue;
                    }
                }
            }
        }

        $xeoBloc->save();

        // Duplicate files so xeo bloc has its own copies
        $basePath = FileService::buildEntityPath($model) . '/' . FileService::buildEntityPath($this->xeo) . '/' . FileService::buildEntityPath($xeoBloc);
        $this->fileService->duplicateBlocFiles($xeoBloc, $basePath);

        // Create pivot
        $xeoBlocPivot = new XeoBloc();
        $xeoBlocPivot->setXeoId($this->xeo->getId());
        $xeoBlocPivot->setBlocId($xeoBloc->getId());
        $xeoBlocPivot->setOrder($order);
        $xeoBlocPivot->save();
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        if ($this->refreshed) {
            return [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Close),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-common'),
                        $this->translator->translate('Structured data refreshed.', category: 'dboard-common'),
                        UiColor::Success
                    ),
                ],
            ];
        }

        return [
            'type' => OutputType::Json->value,
            'data' => [
                ...AureliaCommunication::toast(
                    $this->translator->translate('Error', category: 'dboard-common'),
                    $this->translator->translate('Method not supported.', category: 'dboard-common'),
                    UiColor::Danger
                ),
            ],
        ];
    }
}
