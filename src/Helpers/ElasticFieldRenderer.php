<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Helpers;

use Blackcube\Bleet\Bleet;
use Blackcube\BridgeFormModel\BridgeFormModel;
use Blackcube\Dboard\DboardConfig;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dboard\Widgets\Widgets;
use Blackcube\Injector\Injector;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Html\Html;
use Yiisoft\Translator\TranslatorInterface;

/**
 * Helper for rendering elastic fields from JSON Schema.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ElasticFieldRenderer
{
    private static ?self $instance = null;

    /** @var array<int, ElasticSchema|null> */
    private static array $schemas = [];

    /** @var array<int, string|false> */
    private static array $views = [];

    private readonly Aliases $aliases;
    private readonly ?string $adminTemplatesAlias;
    private readonly TranslatorInterface $translator;

    public function __construct(
        Aliases $aliases,
        DboardConfig $dboardConfig,
        TranslatorInterface $translator,
    ) {
        $this->aliases = $aliases;
        $this->adminTemplatesAlias = $dboardConfig->adminTemplatesAlias;
        $this->translator = $translator;
    }

    private static function getInstance(): self
    {
        self::$instance ??= Injector::get(self::class);
        return self::$instance;
    }

    private const ROUTE_FIELDS = [
        'routes',
        'cmsRoutes',
        'contentRoutes',
        'tagRoutes',
        'regularRoutes',
    ];

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
            if ($property->isElastic($modelClass) === true) {
                $attribute = $prefix . $name;
                $meta = $property->getMeta();
                $field = $meta['field'] ?? 'text';
                if (in_array($field, self::ROUTE_FIELDS, true) === true) {
                    $html .= "\n" . self::renderRouteField($model, $attribute, $field);
                } else {
                    $html .= "\n" . Widgets::elastic($model, $attribute, $elasticOptions)->translator(self::getInstance()->translator)->render();
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

        $field = Bleet::select($model, $attribute)
            ->searchable()
            ->optionsData($options)
            ->prompt('--')
            ->secondary();

        $properties = $model->getProperties();
        $propertyName = preg_replace('/^\[.*\]/', '', $attribute);
        if (isset($properties[$propertyName]) === true) {
            $hint = $properties[$propertyName]->getMeta()['hint'] ?? null;
            if ($hint !== null) {
                $field = $field->hint($hint);
            }
        }

        return Html::div($field->render(), [
            'class' => 'mb-4',
        ])->encode(false)->render();
    }

    /**
     * Get admin view path for a schema with static caching.
     *
     * @param ElasticSchema|int|null $elasticSchemaOrId The elastic schema or its ID
     * @return string|false Template path or false if not found
     */
    public static function getAdminView(ElasticSchema|int|null $elasticSchemaOrId): string|false
    {
        return $elasticSchemaOrId === null ? false : self::getInstance()->buildAdminView($elasticSchemaOrId);
    }

    /**
     * Get schema name from cache.
     *
     * @param ElasticSchema|int|null $elasticSchemaOrId The elastic schema or its ID
     * @return string|null The schema name or null if not found
     */
    public static function getSchemaName(ElasticSchema|int|null $elasticSchemaOrId): ?string
    {
        $schemaName = null;
        if ($elasticSchemaOrId instanceof ElasticSchema) {
            self::$schemas[$elasticSchemaOrId->getId()] = $elasticSchemaOrId;
            $schemaName = $elasticSchemaOrId->getName();
        } elseif ($elasticSchemaOrId !== null) {
            $schemaName = self::loadSchema($elasticSchemaOrId)?->getName();
        }
        return $schemaName;
    }

    /**
     * Get the admin view alias path for display (not resolved).
     *
     * @param ElasticSchema|int|null $elasticSchemaOrId The elastic schema or its ID
     * @return string|null The alias path or null if not configured
     */
    public static function getAdminViewAlias(ElasticSchema|int|null $elasticSchemaOrId): ?string
    {
        return $elasticSchemaOrId === null ? null : self::getInstance()->buildAdminViewAlias($elasticSchemaOrId);
    }

    /**
     * Build the admin view path with caching.
     */
    private function buildAdminView(ElasticSchema|int $elasticSchemaOrId): string|false
    {
        $elasticSchemaId = ($elasticSchemaOrId instanceof ElasticSchema) ? $elasticSchemaOrId->getId() : $elasticSchemaOrId;

        if (isset(self::$views[$elasticSchemaId]) === false) {
            if ($elasticSchemaOrId instanceof ElasticSchema) {
                $elasticSchema = $elasticSchemaOrId;
                self::$schemas[$elasticSchemaId] = $elasticSchema;
            } else {
                $elasticSchema = self::loadSchema($elasticSchemaId);
            }

            if ($elasticSchema === null || $this->adminTemplatesAlias === null) {
                self::$views[$elasticSchemaId] = false;
            } else {
                $viewName = self::viewName($elasticSchema);
                $aliasPath = $this->adminTemplatesAlias . '/' . ucfirst($elasticSchema->getKind()->value) . '/' . $viewName . '.php';
                $filePath = $this->aliases->get($aliasPath);
                self::$views[$elasticSchemaId] = file_exists($filePath) === true ? $filePath : false;
            }
        }

        return self::$views[$elasticSchemaId];
    }

    /**
     * Build the admin view alias path for display.
     */
    private function buildAdminViewAlias(ElasticSchema|int $elasticSchemaOrId): ?string
    {
        $adminViewAlias = null;
        if ($this->adminTemplatesAlias !== null) {
            if ($elasticSchemaOrId instanceof ElasticSchema) {
                $elasticSchema = $elasticSchemaOrId;
                self::$schemas[$elasticSchemaOrId->getId()] = $elasticSchema;
            } else {
                $elasticSchema = self::loadSchema($elasticSchemaOrId);
            }

            if ($elasticSchema !== null) {
                $viewName = self::viewName($elasticSchema);
                $adminViewAlias = $this->adminTemplatesAlias . '/' . ucfirst($elasticSchema->getKind()->value) . '/' . $viewName . '.php';
            }
        }

        return $adminViewAlias;
    }

    /**
     * Load an ElasticSchema with caching.
     */
    private static function loadSchema(int $elasticSchemaId): ?ElasticSchema
    {
        if (array_key_exists($elasticSchemaId, self::$schemas) === false) {
            self::$schemas[$elasticSchemaId] = ElasticSchema::query()->andWhere([
                'id' => $elasticSchemaId,
            ])->one();
        }
        return self::$schemas[$elasticSchemaId];
    }

    /**
     * Resolve the view name from an ElasticSchema.
     */
    private static function viewName(ElasticSchema $elasticSchema): string
    {
        $viewName = $elasticSchema->getView();
        if (empty($viewName) === true) {
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
