<?php

declare(strict_types=1);

/**
 * AbstractXeoSuggest.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dcore\Enums\ElasticSchemaKind;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dcore\Models\SchemaSchema;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Forms\XeoForm;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Method;

/**
 * Abstract action for XEO suggest (auto-fill title/description/image from content elastic data).
 *
 * Two modes:
 * - POST: returns JSON with ajaxify event (triggers <bleet-ajaxify> refresh) + toast
 * - GET:  runs suggest algorithm, returns HTML partial (called by <bleet-ajaxify> component)
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractXeoSuggest extends AbstractAjaxHandler
{
    /**
     * @return string Fully qualified class name of the ActiveRecord model
     */
    abstract protected function getModelClass(): string;

    /**
     * @return string The entity name (e.g., 'content', 'tag')
     */
    abstract protected function getEntityName(): string;

    /**
     * @return string The route prefix (e.g., 'dboard.contents', 'dboard.tags')
     */
    abstract protected function getFileRoutePrefix(): string;

    /**
     * @return string Fully qualified class name (e.g., ContentBloc::class, TagBloc::class)
     */
    abstract protected function getArticleBlocPivotClass(): string;

    /**
     * @return string The FK column (e.g., 'contentId', 'tagId')
     */
    abstract protected function getArticleBlocFkColumn(): string;

    /**
     * @var XeoForm|null The Xeo form model with suggested values
     */
    protected ?XeoForm $formModel = null;

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

        $model = $this->models['main'];

        if ($model->getSlugId() === null) {
            throw new \RuntimeException(ucfirst($this->getEntityName()) . ' must have a slug.');
        }

        $slug = Slug::query()
            ->andWhere(['id' => $model->getSlugId()])
            ->one();

        if ($slug === null) {
            throw new \RuntimeException('Slug not found.');
        }

        // Load existing Xeo form data or create empty
        $xeo = $slug->getXeoQuery()->one();
        if ($xeo !== null) {
            $this->formModel = XeoForm::createFromModel($xeo, $this->translator);
            $this->formModel->setScenario('edit');
        } else {
            $this->formModel = new XeoForm(translator: $this->translator);
            $this->formModel->setSlugId($slug->getId());
            $this->formModel->setScenario('create');
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function setupMethod(): void
    {
        // No form to load
    }

    /**
     * {@inheritdoc}
     */
    protected function handleMethod(): void
    {
        // Only run suggest algorithm on GET (called by <bleet-ajaxify> component)
        if ($this->request->getMethod() === Method::GET) {
            $this->suggest();
        }
    }

    /**
     * Runs the suggest algorithm:
     * 1. Find SchemaSchema mappings → WebPage xeo schema
     * 2. Check article elastic schema → apply mapping
     * 3. If not, check article blocs in order → first match wins
     * 4. Fallback: use article getName() as title
     */
    private function suggest(): void
    {
        $model = $this->models['main'];

        // WebPage field → XeoForm setter
        $webPageToXeo = [
            'name' => 'setTitle',
            'description' => 'setDescription',
            'image' => 'setImage',
        ];

        // 1. Find the WebPage xeo schema
        $webPageSchema = ElasticSchema::query()
            ->andWhere(['name' => 'WebPage', 'kind' => ElasticSchemaKind::Xeo->value])
            ->one();

        if ($webPageSchema === null) {
            $this->formModel->setTitle($model->getName());
            return;
        }

        // 2. Find all SchemaSchema mappings to WebPage, indexed by regularElasticSchemaId
        $schemaSchemas = SchemaSchema::query()
            ->andWhere(['xeoElasticSchemaId' => $webPageSchema->getId()])
            ->all();

        $mappingsByRegularId = [];
        foreach ($schemaSchemas as $ss) {
            $mappingsByRegularId[$ss->getRegularElasticSchemaId()] = $ss;
        }

        if (empty($mappingsByRegularId)) {
            $this->formModel->setTitle($model->getName());
            return;
        }

        $found = false;

        // 3. Check article elastic schema
        $articleSchemaId = $model->getElasticSchemaId();
        if ($articleSchemaId !== null && isset($mappingsByRegularId[$articleSchemaId])) {
            $this->applySuggestMapping($mappingsByRegularId[$articleSchemaId], $model, $webPageToXeo);
            $found = true;
        }

        // 4. If not found, check article blocs (first match wins)
        if (!$found) {
            $pivotClass = $this->getArticleBlocPivotClass();
            $fkColumn = $this->getArticleBlocFkColumn();
            $pivots = $pivotClass::query()
                ->andWhere([$fkColumn => $model->getId()])
                ->orderBy(['order' => SORT_ASC])
                ->all();

            foreach ($pivots as $pivot) {
                $bloc = $pivot->getBlocQuery()->one();
                if ($bloc === null) {
                    continue;
                }
                $blocSchemaId = $bloc->getElasticSchemaId();
                if ($blocSchemaId !== null && isset($mappingsByRegularId[$blocSchemaId])) {
                    $this->applySuggestMapping($mappingsByRegularId[$blocSchemaId], $bloc, $webPageToXeo);
                    $found = true;
                    break;
                }
            }
        }

        // 5. Fallback: use entity name as title
        if (!$found) {
            $this->formModel->setTitle($model->getName());
        }
    }

    /**
     * Applies a SchemaSchema mapping to populate XeoForm from source elastic values.
     */
    private function applySuggestMapping(SchemaSchema $schemaSchema, object $source, array $webPageToXeo): void
    {
        $mappingJson = $schemaSchema->getMapping();
        if ($mappingJson === null) {
            return;
        }

        $decoded = json_decode($mappingJson, true);
        if (!isset($decoded['mapping']) || !is_array($decoded['mapping'])) {
            return;
        }

        foreach ($decoded['mapping'] as $sourceField => $targetField) {
            if (!isset($webPageToXeo[$targetField])) {
                continue;
            }
            $sourceValue = $source->$sourceField ?? null;
            if ($sourceValue !== null && $sourceValue !== '') {
                // Strip HTML tags from description (may come from wysiwyg)
                if ($targetField === 'description') {
                    $sourceValue = trim(strip_tags($sourceValue));
                }
                $setter = $webPageToXeo[$targetField];
                $this->formModel->$setter($sourceValue);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $fileRoutePrefix = $this->getFileRoutePrefix();

        // POST: return JSON with ajaxify event → triggers <bleet-ajaxify> refresh
        if ($this->request->getMethod() === Method::POST) {
            $suggestUrl = $this->urlGenerator->generate(
                $fileRoutePrefix . '.xeo.suggest',
                $this->extractPrimaryKeysFromModel()
            );

            return [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::ajaxify('xeo-suggest', $suggestUrl),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-common'),
                        $this->translator->translate('XEO fields suggested from content data.', category: 'dboard-common'),
                        UiColor::Success
                    ),
                ],
            ];
        }

        // GET: return HTML partial (called by <bleet-ajaxify> component)
        $fileEndpoints = [
            'upload' => $this->urlGenerator->generate($fileRoutePrefix . '.files.upload'),
            'preview' => $this->urlGenerator->generate($fileRoutePrefix . '.files.preview'),
            'delete' => $this->urlGenerator->generate($fileRoutePrefix . '.files.delete'),
        ];

        return [
            'type' => OutputType::Partial->value,
            'view' => 'Commons/_xeo-suggest-content',
            'data' => [
                'formModel' => $this->formModel,
                'fileEndpoints' => $fileEndpoints,
            ],
        ];
    }
}
