<?php

declare(strict_types=1);

/**
 * AbstractXeo.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dcore\Models\Author;
use Blackcube\Dcore\Models\Bloc;
use Blackcube\Dcore\Enums\ElasticSchemaKind;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dcore\Models\LlmMenu;
use Blackcube\Dboard\Models\Forms\XeoLlmForm;
use Blackcube\Dcore\Models\SchemaSchema;
use Blackcube\Dcore\Models\Xeo;
use Blackcube\Dcore\Models\XeoBloc;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dcore\Services\FileService;
use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Forms\XeoAuthorForm;
use Blackcube\Dboard\Models\Forms\XeoBlocForm;
use Blackcube\Dboard\Models\Forms\XeoForm;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Abstract action for Xeo drawer.
 * Inherits from AbstractAjaxHandler and uses ActionModel configuration.
 *
 * Pipeline: setupAction() -> setupMethod() -> try { handleMethod() } catch -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractXeo extends AbstractAjaxHandler
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
     * Returns the author pivot class name.
     *
     * @return string Fully qualified class name (e.g., ContentAuthor::class, TagAuthor::class)
     */
    abstract protected function getAuthorPivotClass(): string;

    /**
     * Returns the FK column name in the author pivot table.
     *
     * @return string The FK column (e.g., 'contentId', 'tagId')
     */
    abstract protected function getAuthorPivotFkColumn(): string;

    /**
     * Returns the FK column name in LlmMenu that points to this entity.
     *
     * @return string The FK column (e.g., 'contentId', 'tagId')
     */
    abstract protected function getLlmMenuFkColumn(): string;

    /**
     * @var Slug|null The slug associated with the model
     */
    protected ?Slug $slug = null;

    /**
     * @var Xeo|null The Xeo record (null if not yet created)
     */
    protected ?Xeo $xeo = null;

    /**
     * @var XeoForm|null The Xeo form model
     */
    protected ?XeoForm $formModel = null;

    /**
     * @var bool Whether the save operation was successful
     */
    protected bool $saved = false;

    /**
     * @var bool Whether the refresh operation was successful
     */
    protected bool $refreshed = false;


    /**
     * @var array<int, Bloc> Xeo bloc models indexed by bloc ID
     */
    protected array $xeoBlocs = [];

    /**
     * @var array<int, XeoBlocForm> Xeo bloc form models indexed by bloc ID
     */
    protected array $xeoBlocForms = [];

    /**
     * @var array<int, ElasticSchema> Elastic schemas indexed by bloc ID
     */
    protected array $xeoBlocSchemas = [];

    /**
     * @var array<int, array<string, string|null>> Initial file values for xeo blocs
     */
    protected array $initialXeoBlocFileValues = [];

    /**
     * @var string|null Path to admin templates for blocs
     */
    protected ?string $adminTemplatesAlias = null;

    /**
     * @var array<int, XeoAuthorForm> Xeo author forms indexed sequentially
     */
    protected array $xeoAuthorForms = [];

    /**
     * @var array<int, Author> Available authors (active, not yet attached)
     */
    protected array $availableAuthors = [];

    /**
     * Creates a new AbstractXeo instance.
     *
     * @param WebViewRenderer $viewRenderer The view renderer
     * @param ResponseFactoryInterface $responseFactory The response factory
     * @param JsonResponseFactory $jsonResponseFactory The JSON response factory
     * @param UrlGeneratorInterface $urlGenerator The URL generator
     * @param Aliases $aliases The aliases service
     * @param FileService $fileService The file save service
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
                formModelClass: null, // Form created manually
                isMain: true, // 404 if not found
            ),
        ];
    }

    /**
     * Sets up the action and prepares the Xeo form.
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

        // Check if model has a slug (required for Xeo)
        if ($model->getSlugId() === null) {
            throw new \RuntimeException(ucfirst($entityName) . ' must have a slug to manage Xeo.');
        }

        $this->slug = Slug::query()
            ->andWhere(['id' => $model->getSlugId()])
            ->one();

        if ($this->slug === null) {
            throw new \RuntimeException('Slug not found.');
        }

        // Load or create Xeo form
        $this->xeo = $this->slug->getXeoQuery()->one();
        if ($this->xeo !== null) {
            $this->formModel = XeoForm::createFromModel($this->xeo, $this->translator);
            $this->formModel->setScenario('edit');
            $this->loadXeoBlocs();
            $this->loadXeoAuthors();
        } else {
            $this->formModel = new XeoForm(translator: $this->translator);
            $this->formModel->setSlugId($this->slug->getId());
            $this->formModel->setScenario('create');
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function setupMethod(): void
    {
        if ($this->request->getMethod() === Method::POST) {
            $bodyParams = $this->getBodyParams();
            $this->formModel->load($bodyParams);
            if (!empty($this->xeoBlocForms)) {
                XeoBlocForm::loadMultiple($this->xeoBlocForms, $bodyParams);
            }
            $this->loadXeoAuthorsFromPost($bodyParams);
        }
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
     * Loads xeo blocs from DB into form models.
     */
    protected function loadXeoBlocs(): void
    {
        $this->xeoBlocs = [];
        $this->xeoBlocForms = [];
        $this->xeoBlocSchemas = [];
        $this->initialXeoBlocFileValues = [];

        foreach ($this->xeo->getXeoBlocsQuery()->orderBy(['order' => SORT_ASC])->each() as $xeoBlocPivot) {
            $bloc = $xeoBlocPivot->getBlocQuery()->one();
            if ($bloc !== null) {
                $blocId = $bloc->getId();
                $this->xeoBlocs[$blocId] = $bloc;
                $this->xeoBlocForms[$blocId] = XeoBlocForm::createFromModel($bloc, $this->translator);
                $this->xeoBlocForms[$blocId]->setScenario('edit');
                $schema = ElasticSchema::query()
                    ->andWhere(['id' => $bloc->getElasticSchemaId()])
                    ->one();
                if ($schema !== null) {
                    $this->xeoBlocSchemas[$blocId] = $schema;
                }
                $this->initialXeoBlocFileValues[$blocId] = $this->fileService->extractFileValues($bloc);
            }
        }
    }

    /**
     * Loads author pivots from DB into XeoAuthorForm instances.
     * Also computes available authors (active, not already attached).
     */
    protected function loadXeoAuthors(): void
    {
        $this->xeoAuthorForms = [];
        $this->availableAuthors = [];

        $model = $this->models['main'];
        $pivotClass = $this->getAuthorPivotClass();
        $fkColumn = $this->getAuthorPivotFkColumn();

        // Load attached author IDs
        $attachedAuthorIds = [];
        $pivots = $pivotClass::query()
            ->andWhere([$fkColumn => $model->getId()])
            ->orderBy(['order' => SORT_ASC])
            ->all();

        foreach ($pivots as $pivot) {
            $author = $pivot->getAuthorQuery()->one();
            if ($author === null) {
                continue;
            }
            $form = new XeoAuthorForm(translator: $this->translator);
            $form->setAuthorId($author->getId());
            $form->setOrder($pivot->getOrder());
            $form->setAuthorFirstname($author->getFirstname());
            $form->setAuthorLastname($author->getLastname());
            $form->setAuthorActive($author->isActive());
            $form->setScenario('xeo');
            $this->xeoAuthorForms[] = $form;
            $attachedAuthorIds[] = $author->getId();
        }

        // Available = active authors not in attached list
        $query = Author::query()->andWhere(['active' => true])->orderBy(['lastname' => SORT_ASC, 'firstname' => SORT_ASC]);
        if (!empty($attachedAuthorIds)) {
            $query->andWhere(['not in', 'id', $attachedAuthorIds]);
        }
        $this->availableAuthors = $query->all();
    }

    /**
     * Rebuilds xeoAuthorForms from POST data.
     * Same pattern as AbstractXeoAuthors: count → for → loadMultiple → populate display.
     */
    protected function loadXeoAuthorsFromPost(array $bodyParams): void
    {
        $formModel = new XeoAuthorForm(translator: $this->translator);
        $formModel->setScenario('xeo');
        $cnt = count($bodyParams[$formModel->getFormName()] ?? []);

        $this->xeoAuthorForms = [];
        for ($i = 0; $i < $cnt; $i++) {
            $form = new XeoAuthorForm(translator: $this->translator);
            $form->setScenario('xeo');
            $this->xeoAuthorForms[] = $form;
        }
        XeoAuthorForm::loadMultiple($this->xeoAuthorForms, $bodyParams);

        // Populate display data
        foreach ($this->xeoAuthorForms as $form) {
            $author = Author::query()
                ->andWhere(['id' => $form->getAuthorId()])
                ->one();
            if ($author !== null) {
                $form->setAuthorFirstname($author->getFirstname());
                $form->setAuthorLastname($author->getLastname());
                $form->setAuthorActive($author->isActive());
            }
        }

        // Recompute available authors
        $authorIds = array_map(fn(XeoAuthorForm $f) => $f->getAuthorId(), $this->xeoAuthorForms);
        $query = Author::query()
            ->andWhere(['active' => true])
            ->orderBy(['lastname' => SORT_ASC, 'firstname' => SORT_ASC]);
        if (!empty($authorIds)) {
            $query->andWhere(['not in', 'id', $authorIds]);
        }
        $this->availableAuthors = $query->all();
    }

    /**
     * Handles xeo blocs refresh: delete existing, recreate from SchemaSchema mappings.
     */
    private function handleRefresh(): void
    {
        $model = $this->models['main'];
        $entityName = $this->getEntityName();

        // Find WebPage xeo schema ID to exclude it (already managed by XEO form)
        $webPageSchema = ElasticSchema::query()
            ->andWhere(['name' => 'WebPage', 'kind' => ElasticSchemaKind::Xeo->value])
            ->one();
        $webPageSchemaId = $webPageSchema?->getId();

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
                    // Skip WebPage xeo schema (managed by XEO form/suggest)
                    if ($webPageSchemaId !== null && $schemaSchema->getXeoElasticSchemaId() === $webPageSchemaId) {
                        continue;
                    }
                    $order = $schemaSchema->getXeoElasticSchemaId();
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
                    // Skip WebPage xeo schema (managed by XEO form/suggest)
                    if ($webPageSchemaId !== null && $schemaSchema->getXeoElasticSchemaId() === $webPageSchemaId) {
                        continue;
                    }
                    $order = $position * 10000 + $schemaSchema->getXeoElasticSchemaId();
                    $this->createXeoBloc($schemaSchema, $bloc, $order);
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->loadXeoBlocs();
        $this->refreshed = true;
    }

    /**
     * Creates a xeo bloc from a SchemaSchema mapping.
     */
    private function createXeoBloc(SchemaSchema $schemaSchema, object $source, int $order): void
    {
        $model = $this->models['main'];
        $entityName = $this->getEntityName();

        $xeoBloc = new Bloc();
        $xeoBloc->setElasticSchemaId($schemaSchema->getXeoElasticSchemaId());
        $xeoBloc->setActive(false);

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

        $xeoBlocPivot = new XeoBloc();
        $xeoBlocPivot->setXeoId($this->xeo->getId());
        $xeoBlocPivot->setBlocId($xeoBloc->getId());
        $xeoBlocPivot->setOrder($order);
        $xeoBlocPivot->save();
    }

    /**
     * {@inheritdoc}
     */
    protected function handleMethod(): void
    {
        if ($this->request->getMethod() !== Method::POST) {
            return;
        }

        // Refresh mode: regenerate xeo blocs from article blocs via SchemaSchema mapping
        if ($this->formModel->isRefresh() && $this->xeo !== null) {
            $this->handleRefresh();
            return;
        }

        // Validate all forms
        $valid = $this->formModel->validate();
        foreach ($this->xeoBlocForms as $xeoBlocForm) {
            if ($xeoBlocForm->isXeoBlocActive() && !$xeoBlocForm->validate()) {
                $valid = false;
            }
        }
        if (!$valid) {
            return; // Form will be re-displayed with errors
        }

        $model = $this->models['main'];
        $entityName = $this->getEntityName();

        if ($this->xeo === null) {
            $this->xeo = new Xeo();
            $this->xeo->setSlugId($this->slug->getId());
        }

        $this->formModel->populateModel($this->xeo);

        $this->beforeSave(false);
        $transaction = $this->xeo->db()->beginTransaction();
        try {
            $this->beforeSave(true);

            $this->xeo->save();

            // Process xeo files: move @bltmp/ -> @blfs/{entityPath}/xeos/{xeoId}/
            $basePath = FileService::buildEntityPath($model) . '/' . FileService::buildEntityPath($this->xeo);
            $this->fileService->processRegularFiles($this->xeo, ['image'], $basePath);
            $this->xeo->save();

            // Save xeo blocs
            foreach ($this->xeoBlocs as $blocId => $bloc) {
                if (isset($this->xeoBlocForms[$blocId])) {
                    $this->xeoBlocForms[$blocId]->populateModel($bloc);
                    $bloc->save();
                    $this->fileService->processBlocFiles($bloc, $model);
                }
            }

            // Save author pivots
            $authorPivotClass = $this->getAuthorPivotClass();
            $authorFkColumn = $this->getAuthorPivotFkColumn();
            $authorPivotSetter = 'set' . ucfirst($authorFkColumn);

            (new $authorPivotClass())->deleteAll([$authorFkColumn => $model->getId()]);

            foreach ($this->xeoAuthorForms as $form) {
                if ($form->validate()) {
                    $pivot = new $authorPivotClass();
                    $form->populateModel($pivot);
                    $pivot->{$authorPivotSetter}($model->getId());
                    $pivot->save();
                }
            }

            $this->afterSave(true);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        $this->afterSave(false);

        // Delete removed xeo bloc files (after transaction success)
        foreach ($this->xeoBlocs as $blocId => $bloc) {
            if (isset($this->initialXeoBlocFileValues[$blocId])) {
                $finalValues = $this->fileService->extractFileValues($bloc);
                $this->fileService->deleteRemovedFiles(
                    $this->initialXeoBlocFileValues[$blocId],
                    $finalValues
                );
            }
        }

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
                        $this->translator->translate('XEO saved.', category: 'dboard-common'),
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

        // Refresh URL (only if Xeo exists)
        $refreshUrl = null;
        if ($this->xeo !== null) {
            $refreshRoute = $fileRoutePrefix . '.xeo.refresh';
            $refreshUrl = $this->urlGenerator->generate(
                $refreshRoute,
                $this->extractPrimaryKeysFromModel()
            );
        }

        // Suggest URL
        $suggestUrl = $this->urlGenerator->generate(
            $fileRoutePrefix . '.xeo.suggest',
            $this->extractPrimaryKeysFromModel()
        );

        // Authors refresh URL
        $authorsRefreshUrl = $this->urlGenerator->generate(
            $fileRoutePrefix . '.xeo.authors',
            $this->extractPrimaryKeysFromModel()
        );

        // LLM data
        $fkColumn = $this->getLlmMenuFkColumn();
        $llmMenu = LlmMenu::query()
            ->andWhere([$fkColumn => $model->getId()])
            ->one();
        $llmCategories = LlmMenu::query()
            ->andWhere(['level' => 2])
            ->orderBy(['left' => SORT_ASC])
            ->all();
        $llmLinkFormModel = new XeoLlmForm(translator: $this->translator);
        $llmRefreshUrl = $this->urlGenerator->generate(
            $fileRoutePrefix . '.xeo.llm',
            $this->extractPrimaryKeysFromModel()
        );

        $header = (string) $this->renderPartial('Commons/_drawer-header', [
            'title' => 'XEO',
            'uiColor' => UiColor::Primary,
        ])->getBody();

        // Canonical slug options
        $canonicalOptions = ['' => $this->translator->translate('No canonical URL', category: 'dboard-common')];
        if ($this->slug !== null) {
            $canonicalOptions[$this->slug->getId()] = $this->translator->translate('Self', category: 'dboard-common');
        }
        foreach (Slug::query()->active()->orderBy(['hostId' => SORT_ASC, 'path' => SORT_ASC])->each() as $s) {
            if ($this->slug === null || $s->getId() !== $this->slug->getId()) {
                $host = $s->getHostQuery()->one();
                $canonicalOptions[$s->getId()] = '//' . $host->getName() . '/' . ltrim($s->getPath(), '/');
            }
        }

        $content = (string) $this->renderPartial('Commons/_xeo-content', [
            'model' => $model,
            'slug' => $this->slug,
            'formModel' => $this->formModel,
            'urlGenerator' => $this->urlGenerator,
            'fileEndpoints' => $fileEndpoints,
            'formAction' => $this->urlGenerator->generate(
                $this->currentRoute->getName(),
                $this->extractPrimaryKeysFromModel()
            ),
            'xeoBlocForms' => $this->xeoBlocForms,
            'xeoBlocSchemas' => $this->xeoBlocSchemas,
            'refreshUrl' => $refreshUrl,
            'suggestUrl' => $suggestUrl,
            'adminTemplatesAlias' => $this->adminTemplatesAlias,
            'canonicalOptions' => $canonicalOptions,
            'xeoAuthorForms' => $this->xeoAuthorForms,
            'availableAuthors' => $this->availableAuthors,
            'authorsRefreshUrl' => $authorsRefreshUrl,
            'llmMenu' => $llmMenu,
            'llmCategories' => $llmCategories,
            'llmLinkFormModel' => $llmLinkFormModel,
            'llmRefreshUrl' => $llmRefreshUrl,
        ])->getBody();

        $data = [
            ...AureliaCommunication::dialog(DialogAction::Keep),
            ...AureliaCommunication::dialogContent($header, $content, UiColor::Primary),
        ];

        if ($this->refreshed) {
            $data = [
                ...$data,
                ...AureliaCommunication::toast(
                    $this->translator->translate('Success', category: 'dboard-common'),
                    $this->translator->translate('Structured data refreshed.', category: 'dboard-common'),
                    UiColor::Success
                ),
            ];
        }

        return [
            'type' => OutputType::Json->value,
            'data' => $data,
        ];
    }
}
