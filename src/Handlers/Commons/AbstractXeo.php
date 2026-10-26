<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dcore\Services\FileService;
use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Handler\OutputType;
use Blackcube\Dboard\Models\Forms\XeoAuthorForm;
use Blackcube\Dboard\Models\Forms\XeoBlocForm;
use Blackcube\Dboard\Models\Forms\XeoForm;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Enums\UiLevel;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
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
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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
     * @var ActiveQueryInterface|null Query of the available authors (active, not yet attached)
     */
    protected ?ActiveQueryInterface $availableAuthorsQuery = null;

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
                formModelClass: null,
                isMain: true,
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
        $setupResponse = parent::setupAction();
        if ($setupResponse === null) {
            $model = $this->models['main'];
            $entityName = $this->getEntityName();

            if ($model->getSlugId() === null) {
                throw new \RuntimeException(ucfirst($entityName) . ' must have a slug to manage Xeo.');
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
        }

        return $setupResponse;
    }

    /**
     * {@inheritdoc}
     */
    protected function setupMethod(): void
    {
        if ($this->request->getMethod() === Method::POST) {
            $bodyParams = $this->getBodyParams();
            $this->formModel->load($bodyParams);
            if (empty($this->xeoBlocForms) === false) {
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
    }

    /**
     * Hook called after save.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function afterSave(bool $inTransaction): void
    {
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

        $xeoBlocsQuery = $this->xeo->getBlocsQuery()->with('elasticSchema');
        /** @var Bloc $bloc */
        foreach ($xeoBlocsQuery->each() as $bloc) {
            $blocId = $bloc->getId();
            $this->xeoBlocs[$blocId] = $bloc;
            $this->xeoBlocForms[$blocId] = XeoBlocForm::createFromModel($bloc, $this->translator);
            $this->xeoBlocForms[$blocId]->setScenario('edit');
            $schema = $bloc->elasticSchema;
            if ($schema !== null) {
                $this->xeoBlocSchemas[$blocId] = $schema;
            }
            $this->initialXeoBlocFileValues[$blocId] = $this->fileService->extractFileValues($bloc);
        }
    }

    /**
     * Loads author pivots from DB into XeoAuthorForm instances.
     * Also computes available authors (active, not already attached).
     */
    protected function loadXeoAuthors(): void
    {
        $this->xeoAuthorForms = [];
        $this->availableAuthorsQuery = null;

        $model = $this->models['main'];
        $pivotClass = $this->getAuthorPivotClass();
        $fkColumn = $this->getAuthorPivotFkColumn();

        $attachedAuthorIds = [];
        $pivotsQuery = $pivotClass::query()
            ->with('author')
            ->andWhere([
                $fkColumn => $model->getId(),
            ])
            ->orderBy([
                'order' => SORT_ASC,
            ]);

        /** @var pivotClass $pivot */
        foreach ($pivotsQuery->each() as $pivot) {
            $author = $pivot->author;
            if ($author !== null) {
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
        }

        $availableAuthorsQuery = Author::query()
            ->andWhere([
                'active' => true,
            ])
            ->orderBy([
                'lastname' => SORT_ASC,
                'firstname' => SORT_ASC,
            ]);
        if (empty($attachedAuthorIds) === false) {
            $availableAuthorsQuery->andWhere([
                'not in',
                'id',
                $attachedAuthorIds,
            ]);
        }
        $this->availableAuthorsQuery = $availableAuthorsQuery;
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

        $postedAuthorIds = [];
        foreach ($this->xeoAuthorForms as $form) {
            $postedAuthorIds[] = $form->getAuthorId();
        }
        $postedAuthors = [];
        if (empty($postedAuthorIds) === false) {
            $postedAuthorsQuery = Author::query()
                ->andWhere([
                    'in',
                    'id',
                    $postedAuthorIds,
                ]);
            /** @var Author $postedAuthor */
            foreach ($postedAuthorsQuery->each() as $postedAuthor) {
                $postedAuthors[$postedAuthor->getId()] = $postedAuthor;
            }
        }

        foreach ($this->xeoAuthorForms as $form) {
            $author = $postedAuthors[$form->getAuthorId()] ?? null;
            if ($author !== null) {
                $form->setAuthorFirstname($author->getFirstname());
                $form->setAuthorLastname($author->getLastname());
                $form->setAuthorActive($author->isActive());
            }
        }

        $availableAuthorsQuery = Author::query()
            ->andWhere([
                'active' => true,
            ])
            ->orderBy([
                'lastname' => SORT_ASC,
                'firstname' => SORT_ASC,
            ]);
        if (empty($postedAuthorIds) === false) {
            $availableAuthorsQuery->andWhere([
                'not in',
                'id',
                $postedAuthorIds,
            ]);
        }
        $this->availableAuthorsQuery = $availableAuthorsQuery;
    }

    /**
     * Handles xeo blocs refresh: delete existing, recreate from SchemaSchema mappings.
     */
    private function handleRefresh(): void
    {
        $model = $this->models['main'];
        $entityName = $this->getEntityName();

        $webPageSchema = ElasticSchema::query()
            ->andWhere([
                'name' => 'WebPage',
                'kind' => ElasticSchemaKind::Xeo->value,
            ])
            ->one();
        $webPageSchemaId = $webPageSchema?->getId();

        $transaction = $this->xeo->db()->beginTransaction();
        try {
            $existingBlocs = [];
            $existingBlocsQuery = $this->xeo->getBlocsQuery();
            /** @var Bloc $bloc */
            foreach ($existingBlocsQuery->each() as $bloc) {
                $existingBlocs[] = $bloc;
            }
            foreach ($existingBlocs as $bloc) {
                $this->fileService->deleteBlocFiles($bloc, $model);
                $this->xeo->detachBloc($bloc);
            }

            $articleSchemaId = $model->getElasticSchemaId();
            if ($articleSchemaId !== null) {
                $schemaSchemasQuery = SchemaSchema::query()
                    ->andWhere([
                        'regularElasticSchemaId' => $articleSchemaId,
                    ])
                    ->orderBy([
                        'xeoElasticSchemaId' => SORT_ASC,
                    ]);
                /** @var SchemaSchema $schemaSchema */
                foreach ($schemaSchemasQuery->each() as $schemaSchema) {
                    if ($webPageSchemaId === null || $schemaSchema->getXeoElasticSchemaId() !== $webPageSchemaId) {
                        $this->createXeoBloc($schemaSchema, $model);
                    }
                }
            }

            $pivotClass = $this->getArticleBlocPivotClass();
            $fkColumn = $this->getArticleBlocFkColumn();
            $articleBlocPivotsQuery = $pivotClass::query()
                ->with('bloc')
                ->andWhere([
                    $fkColumn => $model->getId(),
                ])
                ->orderBy([
                    'order' => SORT_ASC,
                ]);

            $articleBlocs = [];
            $articleBlocSchemaIds = [];
            /** @var pivotClass $pivot */
            foreach ($articleBlocPivotsQuery->each() as $pivot) {
                $bloc = $pivot->bloc;
                if ($bloc !== null && $bloc->getElasticSchemaId() !== null) {
                    $articleBlocs[] = $bloc;
                    $articleBlocSchemaIds[] = $bloc->getElasticSchemaId();
                }
            }

            $blocSchemaSchemas = [];
            if (empty($articleBlocSchemaIds) === false) {
                $blocSchemaSchemasQuery = SchemaSchema::query()
                    ->andWhere([
                        'in',
                        'regularElasticSchemaId',
                        array_values(array_unique($articleBlocSchemaIds)),
                    ])
                    ->orderBy([
                        'xeoElasticSchemaId' => SORT_ASC,
                    ]);
                /** @var SchemaSchema $schemaSchema */
                foreach ($blocSchemaSchemasQuery->each() as $schemaSchema) {
                    $blocSchemaSchemas[$schemaSchema->getRegularElasticSchemaId()][] = $schemaSchema;
                }
            }

            foreach ($articleBlocs as $bloc) {
                if (isset($blocSchemaSchemas[$bloc->getElasticSchemaId()]) === true) {
                    foreach ($blocSchemaSchemas[$bloc->getElasticSchemaId()] as $schemaSchema) {
                        if ($webPageSchemaId === null || $schemaSchema->getXeoElasticSchemaId() !== $webPageSchemaId) {
                            $this->createXeoBloc($schemaSchema, $bloc);
                        }
                    }
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
    private function createXeoBloc(SchemaSchema $schemaSchema, object $source): void
    {
        $model = $this->models['main'];
        $entityName = $this->getEntityName();

        $xeoBloc = new Bloc();
        $xeoBloc->setElasticSchemaId($schemaSchema->getXeoElasticSchemaId());
        $xeoBloc->setActive(false);

        $mappingJson = $schemaSchema->getMapping();
        if ($mappingJson !== null) {
            $decoded = json_decode($mappingJson, true);
            if (isset($decoded['mapping']) === true && is_array($decoded['mapping']) === true) {
                foreach ($decoded['mapping'] as $sourceField => $targetField) {
                    $sourceValue = $source->$sourceField ?? null;
                    if ($sourceValue !== null && $sourceValue !== '') {
                        $xeoBloc->$targetField = $sourceValue;
                    }
                }
            }
        }

        $xeoBloc->save();

        $basePath = FileService::buildEntityPath($model) . '/' . FileService::buildEntityPath($this->xeo) . '/' . FileService::buildEntityPath($xeoBloc);
        $this->fileService->duplicateBlocFiles($xeoBloc, $basePath);

        $this->xeo->attachBloc($xeoBloc, 0);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleMethod(): void
    {
        if ($this->request->getMethod() === Method::POST) {
            if ($this->formModel->isRefresh() === true && $this->xeo !== null) {
                $this->handleRefresh();
            } else {
                $valid = $this->formModel->validate();
                foreach ($this->xeoBlocForms as $xeoBlocForm) {
                    if ($xeoBlocForm->isXeoBlocActive() === true && $xeoBlocForm->validate() === false) {
                        $valid = false;
                    }
                }

                if ($valid === true) {
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

                        $basePath = FileService::buildEntityPath($model) . '/' . FileService::buildEntityPath($this->xeo);
                        $this->fileService->processRegularFiles($this->xeo, [
                            'image',
                        ], $basePath);
                        $this->xeo->save();

                        foreach ($this->xeoBlocs as $blocId => $bloc) {
                            if (isset($this->xeoBlocForms[$blocId]) === true) {
                                $this->xeoBlocForms[$blocId]->populateModel($bloc);
                                $bloc->save();
                                $this->fileService->processBlocFiles($bloc, $model);
                            }
                        }

                        $authorPivotClass = $this->getAuthorPivotClass();
                        $authorFkColumn = $this->getAuthorPivotFkColumn();
                        $authorPivotSetter = 'set' . ucfirst($authorFkColumn);

                        (new $authorPivotClass())->deleteAll([
                            $authorFkColumn => $model->getId(),
                        ]);

                        foreach ($this->xeoAuthorForms as $form) {
                            if ($form->validate() === true) {
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

                    foreach ($this->xeoBlocs as $blocId => $bloc) {
                        if (isset($this->initialXeoBlocFileValues[$blocId]) === true) {
                            $finalValues = $this->fileService->extractFileValues($bloc);
                            $this->fileService->deleteRemovedFiles(
                                $this->initialXeoBlocFileValues[$blocId],
                                $finalValues
                            );
                        }
                    }

                    $this->saved = true;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        if ($this->saved === true) {
            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Close),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-common'),
                        $this->translator->translate('XEO saved.', category: 'dboard-common'),
                        UiLevel::Success
                    ),
                ],
            ];
        } else {
            $model = $this->models['main'];
            $fileRoutePrefix = $this->getFileRoutePrefix();

            $fileEndpoints = [
                'upload' => $this->urlGenerator->generate($fileRoutePrefix . '.files.upload'),
                'preview' => $this->urlGenerator->generate($fileRoutePrefix . '.files.preview'),
                'delete' => $this->urlGenerator->generate($fileRoutePrefix . '.files.delete'),
            ];

            $refreshUrl = null;
            if ($this->xeo !== null) {
                $refreshRoute = $fileRoutePrefix . '.xeo.refresh';
                $refreshUrl = $this->urlGenerator->generate(
                    $refreshRoute,
                    $this->extractPrimaryKeysFromModel()
                );
            }

            $suggestUrl = $this->urlGenerator->generate(
                $fileRoutePrefix . '.xeo.suggest',
                $this->extractPrimaryKeysFromModel()
            );

            $authorsRefreshUrl = $this->urlGenerator->generate(
                $fileRoutePrefix . '.xeo.authors',
                $this->extractPrimaryKeysFromModel()
            );

            $fkColumn = $this->getLlmMenuFkColumn();
            $llmMenu = LlmMenu::query()
                ->andWhere([
                    $fkColumn => $model->getId(),
                ])
                ->one();
            $llmCategoriesQuery = LlmMenu::query()
                ->natural()
                ->andWhere([
                    'in',
                    'level',
                    [
                        1,
                        2,
                    ],
                ]);
            $llmCategories = [];
            /** @var LlmMenu $node */
            foreach ($llmCategoriesQuery->each() as $node) {
                $llmCategories[] = $node;
            }
            $llmLinkFormModel = new XeoLlmForm(translator: $this->translator);
            $llmRefreshUrl = $this->urlGenerator->generate(
                $fileRoutePrefix . '.xeo.llm',
                $this->extractPrimaryKeysFromModel()
            );

            $canonicalOptions = [
                '' => $this->translator->translate('No canonical URL', category: 'dboard-common'),
            ];
            if ($this->slug !== null) {
                $canonicalOptions[$this->slug->getId()] = $this->translator->translate('Self', category: 'dboard-common');
            }
            $canonicalSlugsQuery = Slug::query()->with('host')->active()->orderBy([
                'hostId' => SORT_ASC,
                'path' => SORT_ASC,
            ]);
            /** @var Slug $canonicalSlug */
            foreach ($canonicalSlugsQuery->each() as $canonicalSlug) {
                if ($this->slug === null || $canonicalSlug->getId() !== $this->slug->getId()) {
                    $host = $canonicalSlug->host;
                    $canonicalOptions[$canonicalSlug->getId()] = '//' . $host->getName() . '/' . ltrim($canonicalSlug->getPath(), '/');
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
                'availableAuthorsQuery' => $this->availableAuthorsQuery,
                'authorsRefreshUrl' => $authorsRefreshUrl,
                'llmMenu' => $llmMenu,
                'llmCategories' => $llmCategories,
                'llmLinkFormModel' => $llmLinkFormModel,
                'llmRefreshUrl' => $llmRefreshUrl,
            ])->getBody();

            $aureliaCommunication = [
                ...AureliaCommunication::dialog(DialogAction::Keep),
                ...AureliaCommunication::dialogContent(Bleet::dialogFrame(DialogType::Drawer)
                    ->title('XEO')
                    ->primary()
                    ->content($content)
                    ->render()),
            ];

            if ($this->refreshed === true) {
                $aureliaCommunication = [
                    ...$aureliaCommunication,
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-common'),
                        $this->translator->translate('Structured data refreshed.', category: 'dboard-common'),
                        UiLevel::Success
                    ),
                ];
            }

            $outputData = [
                'type' => OutputType::Json->value,
                'data' => $aureliaCommunication,
            ];
        }

        return $outputData;
    }
}
