<?php

declare(strict_types=1);

/**
 * ElasticFieldRenderer.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Helpers;

use Blackcube\Bleet\Bleet;
use Blackcube\BridgeModel\BridgeFormModel;
use Blackcube\Dboard\DboardConfig;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Injector\Injector;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Html\Html;

/**
 * Helper for rendering elastic fields from JSON Schema.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class ElasticFieldRenderer
{
    private static ?self $instance = null;

    /** @var array<int, ElasticSchema|null> */
    private static array $schemas = [];

    /** @var array<int, string|false> */
    private static array $views = [];

    private readonly Aliases $aliases;
    private readonly ?string $adminTemplatesAlias;

    public function __construct(
        Aliases $aliases,
        DboardConfig $dboardConfig,
    ) {
        $this->aliases = $aliases;
        $this->adminTemplatesAlias = $dboardConfig->adminTemplatesAlias;
    }

    private static function getInstance(): self
    {
        self::$instance ??= Injector::get(self::class);
        return self::$instance;
    }

    private const ROUTE_FIELDS = ['routes', 'cmsRoutes', 'contentRoutes', 'tagRoutes', 'regularRoutes'];

    /**
     * Render all elastic fields for a bridge form model.
     *
     * @param BridgeFormModel $model Form model with elastic properties
     * @param string $prefix Attribute prefix (e.g., '[16]' for bloc forms)
     * @param array $elasticOptions Options (file endpoints, etc.)
     * @return string
     */
    public static function renderAll(BridgeFormModel $model, string $prefix = '', array $elasticOptions = []): string
    {
        $html = '';
        $modelClass = get_class($model);
        foreach ($model->getProperties() as $name => $property) {
            if ($property->isElastic($modelClass)) {
                $attribute = $prefix . $name;
                $meta = $property->getMeta();
                $field = $meta['field'] ?? 'text';
                if (in_array($field, self::ROUTE_FIELDS, true)) {
                    $html .= "\n" . self::renderRouteField($model, $attribute, $field);
                } else {
                    $html .= "\n" . Bleet::elastic($elasticOptions)->active($model, $attribute)->render();
                }
            }
        }

        return $html;
    }

    /**
     * Render a route select field.
     */
    private static function renderRouteField(BridgeFormModel $model, string $attribute, string $field): string
    {
        $routeHelper = Injector::get(RouteHelper::class);
        $options = match ($field) {
            'routes' => $routeHelper->getRoutes(),
            'cmsRoutes' => $routeHelper->getCmsRoutes(),
            'contentRoutes' => $routeHelper->getContentRoutes(),
            'tagRoutes' => $routeHelper->getTagRoutes(),
            'regularRoutes' => $routeHelper->getRegularRoutes(),
        };

        $html = Html::openTag('div', ['class' => 'mb-4']);
        $html .= Bleet::label()->active($model, $attribute)->secondary()->render();
        $html .= Html::openTag('div', ['class' => 'mt-2']);
        $html .= Bleet::select()
            ->searchable()
            ->options($options)
            ->placeholder('--')
            ->active($model, $attribute)
            ->render();
        $html .= Html::closeTag('div');

        $properties = $model->getProperties();
        $propertyName = preg_replace('/^\[.*\]/', '', $attribute);
        if (isset($properties[$propertyName])) {
            $hint = $properties[$propertyName]->getMeta()['hint'] ?? null;
            if ($hint !== null) {
                $html .= Html::tag('p', Html::encode($hint), ['class' => 'mt-1 text-sm text-secondary-500']);
            }
        }

        $html .= Html::closeTag('div');
        return $html;
    }

    /**
     * Get admin view path for a schema with static caching.
     *
     * @param ElasticSchema|int|null $elasticSchemaOrId The elastic schema or its ID
     * @return string|false Template path or false if not found
     */
    public static function getAdminView(ElasticSchema|int|null $elasticSchemaOrId): string|false
    {
        if ($elasticSchemaOrId === null) {
            return false;
        }
        return self::getInstance()->buildAdminView($elasticSchemaOrId);
    }

    /**
     * Get schema name from cache.
     *
     * @param ElasticSchema|int|null $elasticSchemaOrId The elastic schema or its ID
     * @return string|null The schema name or null if not found
     */
    public static function getSchemaName(ElasticSchema|int|null $elasticSchemaOrId): ?string
    {
        if ($elasticSchemaOrId === null) {
            return null;
        }
        if ($elasticSchemaOrId instanceof ElasticSchema) {
            self::$schemas[$elasticSchemaOrId->getId()] = $elasticSchemaOrId;
            return $elasticSchemaOrId->getName();
        }
        return self::loadSchema($elasticSchemaOrId)?->getName();
    }

    /**
     * Get the admin view alias path for display (not resolved).
     *
     * @param ElasticSchema|int|null $elasticSchemaOrId The elastic schema or its ID
     * @return string|null The alias path or null if not configured
     */
    public static function getAdminViewAlias(ElasticSchema|int|null $elasticSchemaOrId): ?string
    {
        if ($elasticSchemaOrId === null) {
            return null;
        }
        return self::getInstance()->buildAdminViewAlias($elasticSchemaOrId);
    }

    /**
     * Build the admin view path with caching.
     */
    private function buildAdminView(ElasticSchema|int $elasticSchemaOrId): string|false
    {
        $elasticSchemaId = ($elasticSchemaOrId instanceof ElasticSchema) ? $elasticSchemaOrId->getId() : $elasticSchemaOrId;

        if (!isset(self::$views[$elasticSchemaId])) {
            if ($elasticSchemaOrId instanceof ElasticSchema) {
                $elasticSchema = $elasticSchemaOrId;
                self::$schemas[$elasticSchemaId] = $elasticSchema;
            } else {
                $elasticSchema = self::loadSchema($elasticSchemaId);
            }

            if ($elasticSchema === null || $this->adminTemplatesAlias === null) {
                self::$views[$elasticSchemaId] = false;
            } else {
                $viewName = self::resolveViewName($elasticSchema);
                $aliasPath = $this->adminTemplatesAlias . '/' . ucfirst($elasticSchema->getKind()->value) . '/' . $viewName . '.php';
                $filePath = $this->aliases->get($aliasPath);
                self::$views[$elasticSchemaId] = file_exists($filePath) ? $filePath : false;
            }
        }

        return self::$views[$elasticSchemaId];
    }

    /**
     * Build the admin view alias path for display.
     */
    private function buildAdminViewAlias(ElasticSchema|int $elasticSchemaOrId): ?string
    {
        if ($this->adminTemplatesAlias === null) {
            return null;
        }

        if ($elasticSchemaOrId instanceof ElasticSchema) {
            $elasticSchema = $elasticSchemaOrId;
            self::$schemas[$elasticSchemaOrId->getId()] = $elasticSchema;
        } else {
            $elasticSchema = self::loadSchema($elasticSchemaOrId);
        }

        if ($elasticSchema === null) {
            return null;
        }

        $viewName = self::resolveViewName($elasticSchema);
        return $this->adminTemplatesAlias . '/' . ucfirst($elasticSchema->getKind()->value) . '/' . $viewName . '.php';
    }

    /**
     * Load an ElasticSchema with caching.
     */
    private static function loadSchema(int $elasticSchemaId): ?ElasticSchema
    {
        if (!array_key_exists($elasticSchemaId, self::$schemas)) {
            self::$schemas[$elasticSchemaId] = ElasticSchema::query()->andWhere(['id' => $elasticSchemaId])->one();
        }
        return self::$schemas[$elasticSchemaId];
    }

    /**
     * Resolve the view name from an ElasticSchema.
     */
    private static function resolveViewName(ElasticSchema $elasticSchema): string
    {
        $viewName = $elasticSchema->getView();
        if (empty($viewName)) {
            $viewName = self::toUnderscore($elasticSchema->getName());
        }

        $viewName = preg_replace('/[-\s]+/', '_', $viewName);

        $transliterator = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC');
        if ($transliterator !== null) {
            $transliterated = $transliterator->transliterate($viewName);
            if ($transliterated !== false) {
                $viewName = $transliterated;
            }
        }

        return $viewName;
    }

    /**
     * Convert string to underscore format.
     */
    private static function toUnderscore(string $string): string
    {
        $string = preg_replace('/^[0-9]+\.\s*/', '', $string);
        $string = preg_replace('/([a-z])([A-Z])/', '$1_$2', $string);
        $string = preg_replace('/[-\s]+/', '_', $string);
        return strtolower($string);
    }
}
