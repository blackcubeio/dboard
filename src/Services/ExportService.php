<?php

declare(strict_types=1);

/**
 * ExportService.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Dcore\Attributes\Exportable;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dcore\Services\FileService;
use DateTimeImmutable;
use ReflectionClass;
use ReflectionMethod;
use Yiisoft\ActiveRecord\ActiveQueryInterface;

/**
 * Service for exporting models to array format using Exportable attributes.
 * Uses reflection to discover exportable properties and relations.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class ExportService
{
    private const FILE_PREFIXES = [FileService::FS_PREFIX, FileService::TMP_PREFIX];

    public function __construct(
        private FileService $fileService,
    ) {}

    /**
     * Exports a model to array format.
     *
     * @param object $model The model to export
     * @return array<string, mixed> The exported data
     * @throws \InvalidArgumentException If model class is not supported
     */
    public function export(object $model): array
    {
        if ($model instanceof Content) {
            $data = ['elementType' => 'content'];
        } elseif ($model instanceof Tag) {
            $data = ['elementType' => 'tag'];
        } else {
            throw new \InvalidArgumentException('Unsupported model class: ' . $model::class);
        }

        return array_merge($data, $this->exportModel($model));
    }

    /**
     * Exports a model using reflection to discover Exportable attributes.
     *
     * @param object $model The model to export
     * @return array<string, mixed> The exported data
     */
    private function exportModel(object $model): array
    {
        $data = [];
        $reflection = new ReflectionClass($model);

        foreach ($this->getExportableMethods($reflection) as $method => $attribute) {
            $name = $this->resolveExportName($method, $attribute);
            $value = $model->$method();
            $data[$name] = $this->processValue($value, $attribute);
        }

        return $data;
    }

    /**
     * Get all methods with Exportable attribute from class hierarchy.
     *
     * @param ReflectionClass $reflection
     * @return array<string, Exportable> Method name => Exportable attribute
     */
    private function getExportableMethods(ReflectionClass $reflection): array
    {
        $methods = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(Exportable::class);
            if (!empty($attributes)) {
                $methods[$method->getName()] = $attributes[0]->newInstance();
            }
        }

        return $methods;
    }

    /**
     * Resolve the export key name from method name and attribute.
     *
     * @param string $methodName The method name
     * @param Exportable $attribute The Exportable attribute
     * @return string The export key name
     */
    private function resolveExportName(string $methodName, Exportable $attribute): string
    {
        if ($attribute->name !== null) {
            return $attribute->name;
        }

        // Convention: getName -> name, isActive -> active, getSlugQuery -> slug
        if (str_starts_with($methodName, 'get')) {
            $name = substr($methodName, 3);
            // Remove Query suffix for relations
            if (str_ends_with($name, 'Query')) {
                $name = substr($name, 0, -5);
            }
            return lcfirst($name);
        }

        if (str_starts_with($methodName, 'is')) {
            return lcfirst(substr($methodName, 2));
        }

        return $methodName;
    }

    /**
     * Process a value for export.
     *
     * @param mixed $value The value to process
     * @param Exportable $attribute The Exportable attribute
     * @return mixed The processed value
     */
    private function processValue(mixed $value, Exportable $attribute): mixed
    {
        if ($value === null) {
            return null;
        }

        // Handle DateTimeImmutable with format
        if ($value instanceof DateTimeImmutable) {
            $format = $attribute->format ?? 'Y-m-d H:i:s';
            return $value->format($format);
        }

        // Handle ActiveQuery - hasOne relation
        if ($value instanceof ActiveQueryInterface) {
            return $this->processActiveQuery($value, $attribute->fields);
        }

        // Handle base64 file encoding (explicit)
        if ($attribute->base64 && is_string($value) && $value !== '') {
            return $this->processFileString($value);
        }

        // Handle arrays (like getElasticValues) - scan for file paths
        if (is_array($value)) {
            return $this->processArrayWithFiles($value);
        }

        return $value;
    }

    /**
     * Process an ActiveQuery for export.
     *
     * @param ActiveQueryInterface $query The query to process
     * @param array<string>|null $fields Limit exported fields to this list
     * @return array<string, mixed>|array<int, array<string, mixed>>|null
     */
    private function processActiveQuery(ActiveQueryInterface $query, ?array $fields = null): array|null
    {
        // Detect if hasOne or hasMany by checking multiple flag
        $reflection = new ReflectionClass($query);
        $multipleProperty = null;

        // Try to get the 'multiple' property from the query
        while ($reflection) {
            if ($reflection->hasProperty('multiple')) {
                $multipleProperty = $reflection->getProperty('multiple');
                $multipleProperty->setAccessible(true);
                break;
            }
            $reflection = $reflection->getParentClass();
        }

        $isMany = $multipleProperty !== null && $multipleProperty->getValue($query) === true;

        if ($isMany) {
            $items = [];
            foreach ($query->each() as $item) {
                $exported = $this->exportModel($item);
                $items[] = $fields !== null ? $this->filterFields($exported, $fields) : $exported;
            }
            return empty($items) ? [] : $items;
        }

        // hasOne
        $item = $query->one();
        if ($item === null) {
            return null;
        }

        $exported = $this->exportModel($item);
        return $fields !== null ? $this->filterFields($exported, $fields) : $exported;
    }

    /**
     * Filter an exported array to only include specified fields.
     *
     * @param array<string, mixed> $data The exported data
     * @param array<string> $fields The fields to keep
     * @return array<string, mixed> The filtered data
     */
    private function filterFields(array $data, array $fields): array
    {
        return array_intersect_key($data, array_flip($fields));
    }

    /**
     * Process an array and convert any file paths to base64.
     *
     * @param array<string, mixed> $data The array to process
     * @return array<string, mixed> The processed array
     */
    private function processArrayWithFiles(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_string($value) && $this->isFilePath($value)) {
                $result[$key] = $this->processFileString($value);
            } elseif (is_array($value)) {
                $result[$key] = $this->processArrayWithFiles($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Check if a string value contains file paths.
     *
     * @param string $value The value to check
     * @return bool True if it contains file paths
     */
    private function isFilePath(string $value): bool
    {
        foreach (self::FILE_PREFIXES as $prefix) {
            if (str_contains($value, $prefix)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Process a file string (single or comma-separated multiple files).
     *
     * @param string $value The file path(s)
     * @return array{name: string, data: string}|array<int, array{name: string, data: string}|null>|null
     */
    private function processFileString(string $value): ?array
    {
        // Check if multiple files (comma-separated)
        $files = preg_split('/\s*,\s*/', $value, -1, PREG_SPLIT_NO_EMPTY);

        if (count($files) === 1) {
            return $this->encodeFileToBase64($files[0]);
        }

        // Multiple files - return array of file objects
        $result = [];
        foreach ($files as $file) {
            $result[] = $this->encodeFileToBase64($file);
        }

        return $result;
    }

    /**
     * Encode a file to base64 data URI with original filename.
     *
     * @param string $filePath The file path (can use aliases like @blfs/)
     * @return array{name: string, data: string}|null The file object or null if not found
     */
    private function encodeFileToBase64(string $filePath): ?array
    {
        // Skip if not a file path alias
        if (!$this->isFilePath($filePath)) {
            return null;
        }

        if (!$this->fileService->fileExists($filePath)) {
            return null;
        }

        $content = $this->fileService->read($filePath);
        $mimeType = $this->fileService->mimeType($filePath);

        return [
            'name' => basename($filePath),
            'data' => 'data:' . $mimeType . ';base64,' . base64_encode($content),
        ];
    }
}
