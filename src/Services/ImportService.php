<?php

declare(strict_types=1);

/**
 * ImportService.php
 *
 * PHP Version 8.2
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Dcore\Models\Author;
use Blackcube\Dcore\Models\Bloc;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\ContentAuthor;
use Blackcube\Dcore\Models\ContentBloc;
use Blackcube\Dcore\Models\ContentTag;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dcore\Models\Host;
use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Models\Sitemap;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dcore\Models\TagAuthor;
use Blackcube\Dcore\Models\TagBloc;
use Blackcube\Dcore\Models\Type;
use Blackcube\Dcore\Models\Xeo;
use Blackcube\Dcore\Models\XeoBloc;
use Blackcube\Dcore\Services\FileService;
use DateTimeImmutable;
use Yiisoft\Db\Connection\ConnectionInterface;

/**
 * Service for importing Content/Tag from JSON export data.
 * Handles parsing, validation and persistence in two transactions.
 */
final class ImportService
{
    public function __construct(
        private ConnectionInterface $db,
        private FileService $fileService,
    ) {}

    /**
     * Parse and validate JSON structure.
     *
     * @return array{valid: bool, elementType: ?string, data: ?array, error: ?string}
     */
    public function parseJson(string $json): array
    {
        $data = json_decode($json, true);
        if ($data === null || !is_array($data)) {
            return ['valid' => false, 'elementType' => null, 'data' => null, 'error' => 'JSON invalide'];
        }

        $elementType = $data['elementType'] ?? null;
        if ($elementType === null || !in_array($elementType, ['content', 'tag'], true)) {
            return ['valid' => false, 'elementType' => null, 'data' => null, 'error' => 'elementType manquant ou invalide'];
        }

        return ['valid' => true, 'elementType' => $elementType, 'data' => $data, 'error' => null];
    }

    /**
     * Check if element already exists by id or slug.path.
     *
     * @return array{existsById: bool, existsBySlug: bool, existingModel: ?object, existingSlug: ?Slug}
     */
    public function checkExistence(array $data): array
    {
        $elementType = $data['elementType'];
        $modelClass = $elementType === 'content' ? Content::class : Tag::class;

        $existsById = false;
        $existsBySlug = false;
        $existingModel = null;
        $existingSlug = null;

        // Check by id
        $id = $data['id'] ?? null;
        if ($id !== null) {
            $existingModel = $modelClass::query()->andWhere(['id' => $id])->one();
            $existsById = $existingModel !== null;
        }

        // Check by slug.path (only if slug data present)
        $slugPath = ($data['slug'] !== null) ? ($data['slug']['path'] ?? null) : null;
        $slugHostId = ($data['slug'] !== null) ? ($data['slug']['hostId'] ?? 1) : 1;
        if ($slugPath !== null) {
            $existingSlug = Slug::query()
                ->andWhere(['path' => $slugPath, 'hostId' => $slugHostId])
                ->one();
            $existsBySlug = $existingSlug !== null;
        }

        return [
            'existsById' => $existsById,
            'existsBySlug' => $existsBySlug,
            'existingModel' => $existingModel,
            'existingSlug' => $existingSlug,
        ];
    }

    /**
     * Validate foreign references and return missing ones.
     *
     * @return array{missing: array, warnings: array}
     */
    public function validateReferences(array $data, array $corrections = []): array
    {
        $missing = [];
        $warnings = [];
        $elementType = $data['elementType'];

        // Language (Content only)
        if ($elementType === 'content') {
            $langId = $corrections['languageId'] ?? $data['languageId'] ?? null;
            if ($langId !== null && Language::query()->andWhere(['id' => $langId])->one() === null) {
                $missing['languageId'] = $langId;
            }
        }

        // Type
        $typeId = $corrections['typeId'] ?? $data['typeId'] ?? null;
        if ($typeId !== null && Type::query()->andWhere(['id' => $typeId])->one() === null) {
            $missing['typeId'] = $typeId;
        }

        // Host (only if slug data present)
        if ($data['slug'] !== null) {
            $hostId = $corrections['hostId'] ?? $data['slug']['hostId'] ?? 1;
            if (Host::query()->andWhere(['id' => $hostId])->one() === null) {
                $missing['hostId'] = $hostId;
            }
        }

        // Authors
        $authors = $data['authors'] ?? [];
        $missingAuthors = [];
        foreach ($authors as $i => $authorData) {
            $authorId = $corrections['authors'][$i] ?? $authorData['id'] ?? null;
            if ($authorId !== null && Author::query()->andWhere(['id' => $authorId])->one() === null) {
                $missingAuthors[$i] = $authorData;
            }
        }
        if (!empty($missingAuthors)) {
            $missing['authors'] = $missingAuthors;
        }

        // Tags (Content only) — warning, not blocking
        if ($elementType === 'content') {
            $tags = $data['tags'] ?? [];
            $missingTags = [];
            foreach ($tags as $tagData) {
                $tagId = $tagData['id'] ?? null;
                if ($tagId !== null && Tag::query()->andWhere(['id' => $tagId])->one() === null) {
                    $missingTags[] = $tagData;
                }
            }
            if (!empty($missingTags)) {
                $warnings['tags'] = $missingTags;
            }
        }

        // Bloc elasticSchemaIds
        $resolvedTypeId = $corrections['typeId'] ?? $data['typeId'] ?? null;
        $blocs = $data['blocs'] ?? [];
        $missingSchemas = [];
        foreach ($blocs as $i => $blocData) {
            $schemaId = $corrections['blocs'][$i] ?? $blocData['elasticSchemaId'] ?? null;
            if ($schemaId !== null && ElasticSchema::query()->andWhere(['id' => $schemaId])->one() === null) {
                $missingSchemas[$i] = $blocData;
            }
        }
        if (!empty($missingSchemas)) {
            $missing['blocs'] = $missingSchemas;
        }

        // Xeo bloc elasticSchemaIds (only if slug data present)
        $xeoBlocs = ($data['slug'] !== null) ? ($data['slug']['xeo']['blocs'] ?? []) : [];
        $missingXeoSchemas = [];
        foreach ($xeoBlocs as $i => $blocData) {
            $schemaId = $corrections['xeoBlocs'][$i] ?? $blocData['elasticSchemaId'] ?? null;
            if ($schemaId !== null && ElasticSchema::query()->andWhere(['id' => $schemaId])->one() === null) {
                $missingXeoSchemas[$i] = $blocData;
            }
        }
        if (!empty($missingXeoSchemas)) {
            $missing['xeoBlocs'] = $missingXeoSchemas;
        }

        return ['missing' => $missing, 'warnings' => $warnings];
    }

    /**
     * Execute the import.
     *
     * @param array $data The parsed JSON data
     * @param string $mode 'overwrite' or 'create'
     * @param string|null $targetPath Hazeltree target path for positioning
     * @param array $corrections Corrected references from step 3
     * @return array{success: bool, model: ?object, error: ?string}
     */
    public function execute(array $data, string $mode, ?string $targetPath, array $corrections): array
    {
        $elementType = $data['elementType'];
        $isContent = $elementType === 'content';
        $modelClass = $isContent ? Content::class : Tag::class;

        // Apply corrections to data
        $data = $this->applyCorrections($data, $corrections);

        // Transaction 1: Create records (no files)
        $transaction = $this->db->beginTransaction();
        try {
            $model = null;
            $slug = null;
            $xeo = null;
            $sitemap = null;
            $createdBlocs = [];
            $createdXeoBlocs = [];

            if ($mode === 'overwrite') {
                $model = $this->loadExistingModel($data, $modelClass);
                if ($model === null) {
                    throw new \RuntimeException('Modèle existant introuvable pour écrasement');
                }
                $this->cleanExistingData($model, $isContent);
                $slug = $model->getSlugQuery()->one();
            }

            // Create/update Slug (only if slug data present)
            if ($data['slug'] !== null) {
                $slug = $this->saveSlug($data, $slug);
                $xeo = $this->saveXeo($data, $slug);
                $sitemap = $this->saveSitemap($data, $slug);
            }

            // Create/update model
            if ($mode === 'overwrite') {
                $model = $this->updateModel($model, $data, $slug, $isContent);
            } else {
                $model = $this->createModel($data, $slug, $modelClass, $targetPath);
            }

            // Create article blocs (text only, no files)
            $blocs = $data['blocs'] ?? [];
            foreach ($blocs as $i => $blocData) {
                $bloc = $this->createBloc($blocData, true);
                $createdBlocs[$i] = $bloc;
                if ($isContent) {
                    $model->attachBloc($bloc);
                } else {
                    $model->attachBloc($bloc);
                }
            }

            // Create xeo blocs (only if xeo exists)
            $xeoBlocs = ($xeo !== null) ? ($data['slug']['xeo']['blocs'] ?? []) : [];
            foreach ($xeoBlocs as $i => $blocData) {
                $bloc = $this->createBloc($blocData, true);
                $createdXeoBlocs[$i] = $bloc;
                $xeoBlocPivot = new XeoBloc();
                $xeoBlocPivot->setXeoId($xeo->getId());
                $xeoBlocPivot->setBlocId($bloc->getId());
                $xeoBlocPivot->setOrder($i + 1);
                $xeoBlocPivot->save();
            }

            // Attach tags (Content only, skip missing)
            if ($isContent) {
                $this->attachTags($model, $data['tags'] ?? []);
            }

            // Attach authors
            $this->attachAuthors($model, $data['authors'] ?? [], $isContent);

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return ['success' => false, 'model' => null, 'error' => $e->getMessage()];
        }

        // Transaction 2: Upload files and update paths
        $entityType = $isContent ? 'contents' : 'tags';
        $modelId = $model->getId();

        $transaction2 = $this->db->beginTransaction();
        try {
            // Process article bloc files
            foreach ($createdBlocs as $i => $bloc) {
                $blocData = $blocs[$i];
                $this->processBlocFiles($bloc, $blocData['data'] ?? [], $entityType, $modelId);
            }

            // Process xeo bloc files
            foreach ($createdXeoBlocs as $i => $bloc) {
                $blocData = $xeoBlocs[$i];
                $this->processBlocFiles($bloc, $blocData['data'] ?? [], $entityType, $modelId);
            }

            // Process model elastic files: @blfs/{entityType}/{modelId}/{filename}
            $modelElasticData = $data['data'] ?? [];
            $this->processModelFiles($model, $modelElasticData, $entityType, $modelId);

            // Process xeo image: @blfs/{entityType}/{modelId}/xeo/{xeoId}/{filename}
            if ($xeo !== null) {
                $xeoData = $data['slug']['xeo'] ?? [];
                $imageData = $xeoData['image'] ?? null;
                if ($imageData !== null) {
                    $basePath = $entityType . '/' . $modelId . '/xeo/' . $xeo->getId();
                    $dataUri = null;
                    $filename = null;

                    // {name, data} object
                    if (is_array($imageData) && isset($imageData['data'])) {
                        $dataUri = $imageData['data'];
                        $filename = $imageData['name'] ?? null;
                    // Legacy: plain data: string
                    } elseif (is_string($imageData) && str_starts_with($imageData, 'data:')) {
                        $dataUri = $imageData;
                    }

                    if ($dataUri !== null) {
                        $path = $this->decodeBase64FileRaw($dataUri, $basePath, $filename);
                        if ($path !== null) {
                            $xeo->setImage($path);
                            $xeo->save();
                        }
                    }
                }
            }

            $transaction2->commit();
        } catch (\Throwable $e) {
            $transaction2->rollBack();
            return ['success' => false, 'model' => $model, 'error' => 'Import partiel — erreur fichiers : ' . $e->getMessage()];
        }

        return ['success' => true, 'model' => $model, 'error' => null];
    }

    /**
     * Apply corrections from step 3 onto the data.
     */
    private function applyCorrections(array $data, array $corrections): array
    {
        if (isset($corrections['languageId'])) {
            $data['languageId'] = $corrections['languageId'];
        }
        if (isset($corrections['typeId'])) {
            $data['typeId'] = $corrections['typeId'];
        }
        if (isset($corrections['hostId']) && $data['slug'] !== null) {
            $data['slug']['hostId'] = $corrections['hostId'];
        }
        if (isset($corrections['slugPath']) && $data['slug'] !== null) {
            $data['slug']['path'] = $corrections['slugPath'];
        }
        if (isset($corrections['authors'])) {
            foreach ($corrections['authors'] as $i => $authorId) {
                if (isset($data['authors'][$i])) {
                    $data['authors'][$i]['id'] = $authorId;
                }
            }
        }
        if (isset($corrections['blocs'])) {
            foreach ($corrections['blocs'] as $i => $schemaId) {
                if (isset($data['blocs'][$i])) {
                    $data['blocs'][$i]['elasticSchemaId'] = $schemaId;
                }
            }
        }
        if (isset($corrections['xeoBlocs']) && $data['slug'] !== null) {
            foreach ($corrections['xeoBlocs'] as $i => $schemaId) {
                if (isset($data['slug']['xeo']['blocs'][$i])) {
                    $data['slug']['xeo']['blocs'][$i]['elasticSchemaId'] = $schemaId;
                }
            }
        }

        return $data;
    }

    private function loadExistingModel(array $data, string $modelClass): ?object
    {
        $id = $data['id'] ?? null;
        if ($id !== null) {
            $model = $modelClass::query()->andWhere(['id' => $id])->one();
            if ($model !== null) {
                return $model;
            }
        }

        $slugPath = ($data['slug'] !== null) ? ($data['slug']['path'] ?? null) : null;
        if ($slugPath !== null) {
            $slug = Slug::query()->andWhere(['path' => $slugPath])->one();
            if ($slug !== null) {
                return $slug->getElement();
            }
        }

        return null;
    }

    /**
     * Clean existing data before overwrite.
     */
    private function cleanExistingData(object $model, bool $isContent): void
    {
        // Detach all blocs
        $blocs = $model->getBlocsQuery()->all();
        foreach ($blocs as $bloc) {
            $model->detachBloc($bloc);
        }

        // Clean xeo blocs
        $slug = $model->getSlugQuery()->one();
        if ($slug !== null) {
            $xeo = $slug->getXeoQuery()->one();
            if ($xeo !== null) {
                $xeoBlocs = $xeo->getXeoBlocsQuery()->all();
                foreach ($xeoBlocs as $xeoBlocPivot) {
                    $bloc = $xeoBlocPivot->getBlocQuery()->one();
                    $xeoBlocPivot->delete();
                    if ($bloc !== null) {
                        $bloc->delete();
                    }
                }
            }
        }

        // Detach tags (Content only)
        if ($isContent && method_exists($model, 'syncTags')) {
            $model->syncTags([]);
        }

        // Detach authors
        $pivotClass = $isContent ? ContentAuthor::class : TagAuthor::class;
        $fkColumn = $isContent ? 'contentId' : 'tagId';
        $pivots = $pivotClass::query()->andWhere([$fkColumn => $model->getId()])->all();
        foreach ($pivots as $pivot) {
            $pivot->delete();
        }
    }

    private function saveSlug(array $data, ?Slug $existing): Slug
    {
        $slugData = $data['slug'] ?? [];
        $slug = $existing ?? new Slug();

        $slug->setHostId($slugData['hostId'] ?? 1);
        $slug->setPath($slugData['path'] ?? '');
        $slug->setTargetUrl($slugData['targetUrl'] ?? null);
        $slug->setHttpCode($slugData['httpCode'] ?? null);
        $slug->setActive($slugData['active'] ?? true);
        $slug->save();

        return $slug;
    }

    private function saveXeo(array $data, Slug $slug): ?Xeo
    {
        $xeoData = $data['slug']['xeo'] ?? null;
        if ($xeoData === null) {
            return null;
        }

        $xeo = Xeo::query()->andWhere(['slugId' => $slug->getId()])->one();
        if ($xeo === null) {
            $xeo = new Xeo();
            $xeo->setSlugId($slug->getId());
        }

        // Handle canonical
        $canonical = $xeoData['canonical'] ?? false;
        $xeo->setCanonicalSlugId($canonical ? $slug->getId() : null);

        $xeo->setTitle($xeoData['title'] ?? null);
        // Image handled in transaction 2
        $xeo->setDescription($xeoData['description'] ?? null);
        $xeo->setNoindex($xeoData['noindex'] ?? false);
        $xeo->setNofollow($xeoData['nofollow'] ?? false);
        $xeo->setOg($xeoData['og'] ?? false);
        $xeo->setOgType($xeoData['ogType'] ?? null);
        $xeo->setTwitter($xeoData['twitter'] ?? false);
        $xeo->setTwitterCard($xeoData['twitterCard'] ?? null);
        $xeo->setJsonldType($xeoData['jsonldType'] ?? 'WebPage');
        $xeo->setSpeakable($xeoData['speakable'] ?? false);
        $xeo->setKeywords($xeoData['keywords'] ?? null);
        $xeo->setAccessibleForFree($xeoData['accessibleForFree'] ?? true);
        $xeo->setActive($xeoData['active'] ?? false);
        $xeo->save();

        return $xeo;
    }

    private function saveSitemap(array $data, Slug $slug): ?Sitemap
    {
        $sitemapData = $data['slug']['sitemap'] ?? null;
        if ($sitemapData === null) {
            return null;
        }

        $sitemap = Sitemap::query()->andWhere(['slugId' => $slug->getId()])->one();
        if ($sitemap === null) {
            $sitemap = new Sitemap();
            $sitemap->setSlugId($slug->getId());
        }

        $sitemap->setFrequency($sitemapData['frequency'] ?? 'daily');
        $sitemap->setPriority((float) ($sitemapData['priority'] ?? 0.5));
        $sitemap->setActive($sitemapData['active'] ?? false);
        $sitemap->save();

        return $sitemap;
    }

    private function createModel(array $data, ?Slug $slug, string $modelClass, ?string $targetPath): object
    {
        $model = new $modelClass();
        $isContent = $modelClass === Content::class;

        $model->setName($data['name'] ?? '');
        $model->setSlugId($slug?->getId());
        $model->setTypeId($data['typeId'] ?? null);
        $model->setElasticSchemaId($data['elasticSchemaId'] ?? null);
        $model->setActive(false); // Always inactive on import

        if ($isContent) {
            $model->setLanguageId($data['languageId'] ?? null);
            $dateStart = isset($data['dateStart']) ? new DateTimeImmutable($data['dateStart']) : null;
            $dateEnd = isset($data['dateEnd']) ? new DateTimeImmutable($data['dateEnd']) : null;
            $model->setDateStart($dateStart);
            $model->setDateEnd($dateEnd);
        }

        // Hazeltree positioning
        if ($targetPath !== null) {
            $target = $modelClass::query()->andWhere(['path' => $targetPath])->one();
            if ($target !== null) {
                // Check if target path from JSON already exists → insert after
                $jsonPath = $data['path'] ?? null;
                if ($jsonPath !== null) {
                    $existing = $modelClass::query()->andWhere(['path' => $jsonPath])->one();
                    if ($existing !== null) {
                        $model->saveAfter($target);
                    } else {
                        $model->saveInto($target);
                    }
                } else {
                    $model->saveInto($target);
                }
            } else {
                $model->save();
            }
        } else {
            $model->save();
        }

        // Set elastic data (text only, files stripped — handled in transaction 2)
        $elasticData = $data['data'] ?? [];
        if (!empty($elasticData)) {
            $stripped = $this->stripFileValues($elasticData);
            foreach ($stripped as $key => $value) {
                $model->$key = $value;
            }
            $model->save();
        }

        return $model;
    }

    private function updateModel(object $model, array $data, ?Slug $slug, bool $isContent): object
    {
        $model->setName($data['name'] ?? '');
        $model->setSlugId($slug?->getId());
        $model->setTypeId($data['typeId'] ?? null);
        $model->setElasticSchemaId($data['elasticSchemaId'] ?? null);
        $model->setActive(false); // Always inactive on overwrite

        if ($isContent) {
            $model->setLanguageId($data['languageId'] ?? null);
            $dateStart = isset($data['dateStart']) ? new DateTimeImmutable($data['dateStart']) : null;
            $dateEnd = isset($data['dateEnd']) ? new DateTimeImmutable($data['dateEnd']) : null;
            $model->setDateStart($dateStart);
            $model->setDateEnd($dateEnd);
        }

        $model->save();

        // Set elastic data (text only, files stripped — handled in transaction 2)
        $elasticData = $data['data'] ?? [];
        if (!empty($elasticData)) {
            $stripped = $this->stripFileValues($elasticData);
            foreach ($stripped as $key => $value) {
                $model->$key = $value;
            }
            $model->save();
        }

        return $model;
    }

    /**
     * Create a bloc with elastic data but without processing files.
     */
    private function createBloc(array $blocData, bool $skipFiles): Bloc
    {
        $bloc = new Bloc();
        $bloc->setElasticSchemaId($blocData['elasticSchemaId'] ?? null);
        $bloc->setActive(true);
        $bloc->save();

        // Set elastic values via magic __set (text only in transaction 1)
        $elasticData = $blocData['data'] ?? [];
        if ($skipFiles) {
            $elasticData = $this->stripFileValues($elasticData);
        }
        foreach ($elasticData as $key => $value) {
            $bloc->$key = $value;
        }
        $bloc->save();

        return $bloc;
    }

    /**
     * Strip base64 file values from elastic data.
     * Handles both {name, data} objects and legacy data: strings.
     */
    private function stripFileValues(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_string($value) && str_starts_with($value, 'data:')) {
                $result[$key] = null;
            } elseif (is_array($value) && isset($value['data']) && is_string($value['data'])) {
                // Single file object {name, data}
                $result[$key] = null;
            } elseif (is_array($value) && isset($value[0]) && is_array($value[0]) && isset($value[0]['data'])) {
                // Multi files [{name, data}, ...]
                $result[$key] = null;
            } elseif (is_array($value)) {
                $result[$key] = $this->stripFileValues($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Process base64 files for a bloc and update elastic values.
     * Handles {name, data} objects, [{name, data}, ...] arrays, and legacy data: strings.
     *
     * @param string $entityType e.g. 'contents', 'tags'
     * @param int $entityId The parent model id
     */
    private function processBlocFiles(Bloc $bloc, array $elasticData, string $entityType, int $entityId): void
    {
        $hasFiles = false;

        foreach ($elasticData as $key => $value) {
            // Single file object {name, data}
            if (is_array($value) && isset($value['data']) && is_string($value['data'])) {
                $path = $this->decodeBase64File($value['data'], $entityType, $entityId, $bloc->getId(), $value['name'] ?? null);
                if ($path !== null) {
                    $bloc->$key = $path;
                    $hasFiles = true;
                }
            // Multi files [{name, data}, ...] → join with ', '
            } elseif (is_array($value) && isset($value[0]) && is_array($value[0]) && isset($value[0]['data'])) {
                $paths = [];
                foreach ($value as $fileObj) {
                    $path = $this->decodeBase64File($fileObj['data'], $entityType, $entityId, $bloc->getId(), $fileObj['name'] ?? null);
                    if ($path !== null) {
                        $paths[] = $path;
                    }
                }
                if (!empty($paths)) {
                    $bloc->$key = implode(', ', $paths);
                    $hasFiles = true;
                }
            // Legacy: plain data: string
            } elseif (is_string($value) && str_starts_with($value, 'data:')) {
                $path = $this->decodeBase64File($value, $entityType, $entityId, $bloc->getId());
                if ($path !== null) {
                    $bloc->$key = $path;
                    $hasFiles = true;
                }
            }
        }

        if ($hasFiles) {
            $bloc->save();
        }
    }

    /**
     * Process base64 files for a model's elastic data (no blocId).
     * Files go to @blfs/{entityType}/{entityId}/{filename}
     */
    private function processModelFiles(object $model, array $elasticData, string $entityType, int $entityId): void
    {
        $hasFiles = false;

        foreach ($elasticData as $key => $value) {
            // Single file object {name, data}
            if (is_array($value) && isset($value['data']) && is_string($value['data'])) {
                $path = $this->decodeBase64File($value['data'], $entityType, $entityId, null, $value['name'] ?? null);
                if ($path !== null) {
                    $model->$key = $path;
                    $hasFiles = true;
                }
            // Multi files [{name, data}, ...] → join with ', '
            } elseif (is_array($value) && isset($value[0]) && is_array($value[0]) && isset($value[0]['data'])) {
                $paths = [];
                foreach ($value as $fileObj) {
                    $path = $this->decodeBase64File($fileObj['data'], $entityType, $entityId, null, $fileObj['name'] ?? null);
                    if ($path !== null) {
                        $paths[] = $path;
                    }
                }
                if (!empty($paths)) {
                    $model->$key = implode(', ', $paths);
                    $hasFiles = true;
                }
            // Legacy: plain data: string
            } elseif (is_string($value) && str_starts_with($value, 'data:')) {
                $path = $this->decodeBase64File($value, $entityType, $entityId);
                if ($path !== null) {
                    $model->$key = $path;
                    $hasFiles = true;
                }
            }
        }

        if ($hasFiles) {
            $model->save();
        }
    }

    /**
     * Decode a base64 data URI and write the file via FileService.
     *
     * @param string $entityType e.g. 'contents', 'tags'
     * @param int $entityId The model id
     * @param int|null $blocId If file belongs to a bloc
     * @param string|null $filename Original filename (used instead of hash if provided)
     * @return string|null The @blfs/ path or null on failure
     */
    private function decodeBase64File(string $dataUri, string $entityType, int $entityId, ?int $blocId = null, ?string $filename = null): ?string
    {
        // Format: data:mime/type;base64,CONTENT
        if (!preg_match('#^data:([^;]+);base64,(.+)$#s', $dataUri, $matches)) {
            return null;
        }

        $mimeType = $matches[1];
        $content = base64_decode($matches[2], true);
        if ($content === false) {
            return null;
        }

        if ($filename === null || $filename === '') {
            $ext = $this->mimeToExtension($mimeType);
            $filename = bin2hex(random_bytes(8)) . '.' . $ext;
        }

        // Convention: @blfs/{entityType}/{entityId}/blocs/{blocId}/{filename}
        //         or: @blfs/{entityType}/{entityId}/{filename}
        $path = FileService::FS_PREFIX . $entityType . '/' . $entityId;
        if ($blocId !== null) {
            $path .= '/blocs/' . $blocId;
        }
        $path .= '/' . $filename;

        $this->fileService->write($path, $content);

        return $path;
    }

    /**
     * Decode a base64 data URI and write to a custom base path.
     *
     * @param string|null $filename Original filename (used instead of hash if provided)
     */
    private function decodeBase64FileRaw(string $dataUri, string $basePath, ?string $filename = null): ?string
    {
        if (!preg_match('#^data:([^;]+);base64,(.+)$#s', $dataUri, $matches)) {
            return null;
        }

        $mimeType = $matches[1];
        $content = base64_decode($matches[2], true);
        if ($content === false) {
            return null;
        }

        if ($filename === null || $filename === '') {
            $ext = $this->mimeToExtension($mimeType);
            $filename = bin2hex(random_bytes(8)) . '.' . $ext;
        }

        $path = FileService::FS_PREFIX . $basePath . '/' . $filename;

        $this->fileService->write($path, $content);

        return $path;
    }

    private function mimeToExtension(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/avif' => 'avif',
            'image/svg+xml' => 'svg',
            'application/pdf' => 'pdf',
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            default => 'bin',
        };
    }

    private function attachTags(object $model, array $tagsData): void
    {
        foreach ($tagsData as $tagData) {
            $tagId = $tagData['id'] ?? null;
            if ($tagId === null) {
                continue;
            }
            $tag = Tag::query()->andWhere(['id' => $tagId])->one();
            if ($tag !== null) {
                $model->attachTag($tag);
            }
        }
    }

    private function attachAuthors(object $model, array $authorsData, bool $isContent): void
    {
        $pivotClass = $isContent ? ContentAuthor::class : TagAuthor::class;
        $fkColumn = $isContent ? 'contentId' : 'tagId';
        $order = 1;

        foreach ($authorsData as $authorData) {
            $authorId = $authorData['id'] ?? null;
            if ($authorId === null) {
                continue;
            }
            $author = Author::query()->andWhere(['id' => $authorId])->one();
            if ($author !== null) {
                $pivot = new $pivotClass();
                if ($isContent) {
                    $pivot->setContentId($model->getId());
                } else {
                    $pivot->setTagId($model->getId());
                }
                $pivot->setAuthorId($author->getId());
                $pivot->setOrder($order++);
                $pivot->save();
            }
        }
    }

    /**
     * Check if a slug path is already taken for a given host.
     */
    public function isSlugPathTaken(string $path, int $hostId): bool
    {
        return Slug::query()
            ->andWhere(['path' => $path, 'hostId' => $hostId])
            ->one() !== null;
    }

    /**
     * Get available tree targets for positioning.
     *
     * @return array<int, array{path: string, name: string, level: int}>
     */
    public function getTreeTargets(string $elementType): array
    {
        $modelClass = $elementType === 'content' ? Content::class : Tag::class;
        $targets = [];

        foreach ($modelClass::query()->orderBy(['left' => SORT_ASC])->each() as $item) {
            $targets[] = [
                'path' => $item->path,
                'name' => $item->getName() ?? '(sans nom)',
                'level' => $item->level,
            ];
        }

        return $targets;
    }

    /**
     * Get dropdown options for references.
     */
    public function getReferenceOptions(): array
    {
        $languages = [];
        foreach (Language::query()->active()->orderBy(['name' => SORT_ASC])->each() as $lang) {
            $languages[$lang->getId()] = $lang->getName();
        }

        $types = [];
        foreach (Type::query()->active()->orderBy(['name' => SORT_ASC])->each() as $type) {
            $types[$type->getId()] = $type->getName();
        }

        $hosts = [];
        foreach (Host::query()->active()->orderBy(['name' => SORT_ASC])->each() as $host) {
            $hosts[$host->getId()] = $host->getName();
        }

        $authors = [];
        foreach (Author::query()->active()->orderBy(['lastname' => SORT_ASC, 'firstname' => SORT_ASC])->each() as $author) {
            $authors[$author->getId()] = trim($author->getFirstname() . ' ' . $author->getLastname());
        }

        $elasticSchemas = [];
        foreach (ElasticSchema::query()->active()->orderBy(['name' => SORT_ASC])->each() as $schema) {
            $elasticSchemas[$schema->getId()] = $schema->getName();
        }

        return [
            'languages' => $languages,
            'types' => $types,
            'hosts' => $hosts,
            'authors' => $authors,
            'elasticSchemas' => $elasticSchemas,
        ];
    }
}
