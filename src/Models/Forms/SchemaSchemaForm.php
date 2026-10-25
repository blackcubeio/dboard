<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\Dcore\Models\SchemaSchema;
use Blackcube\BridgeFormModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Result;

/**
 * SchemaSchema form model.
 * Composite PK: regularElasticSchemaId + xeoElasticSchemaId.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class SchemaSchemaForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-modules';

    protected int|string $regularElasticSchemaId = '';
    protected int|string $xeoElasticSchemaId = '';
    protected string $mapping = '';

    #[Bridge]
    public function setRegularElasticSchemaId(int|string $regularElasticSchemaId): void
    {
        $this->regularElasticSchemaId = $regularElasticSchemaId;
    }
    #[Bridge]
    public function getRegularElasticSchemaId(): int
    {
        return (int) $this->regularElasticSchemaId;
    }
    #[Bridge]
    public function setXeoElasticSchemaId(int|string $xeoElasticSchemaId): void
    {
        $this->xeoElasticSchemaId = $xeoElasticSchemaId;
    }
    #[Bridge]
    public function getXeoElasticSchemaId(): int
    {
        return (int) $this->xeoElasticSchemaId;
    }
    #[Bridge]
    public function setMapping(string $mapping): void
    {
        $this->mapping = $mapping;
    }
    #[Bridge]
    public function getMapping(): string
    {
        return $this->mapping;
    }

    public function scenarios(): array
    {
        return [
            'create' => [
                'regularElasticSchemaId',
                'xeoElasticSchemaId',
                'mapping',
            ],
            'edit' => [
                'mapping',
            ],
        ];
    }

    public function rules(): array
    {
        $rules = [
            'regularElasticSchemaId' => [
                new Required(),
            ],
            'xeoElasticSchemaId' => [
                new Required(),
            ],
            'mapping' => [
                new Required(),
                new Length(max: 65535),
                new Callback(callback: $this->checkMappingJson(...)),
            ],
        ];

        if ($this->getScenario() === 'create') {
            $rules['xeoElasticSchemaId'][] = new Callback(callback: $this->checkUniqueAssociation(...));
        }

        return $rules;
    }

    protected function checkMappingJson(mixed $mapping): Result
    {
        $validationResult = new Result();
        if ($mapping !== '' && json_decode($mapping) === null && json_last_error() !== JSON_ERROR_NONE) {
            $validationResult->addError('Mapping must be valid JSON.');
        }
        return $validationResult;
    }

    protected function checkUniqueAssociation(): Result
    {
        $validationResult = new Result();
        $associationExists = SchemaSchema::query()
            ->andWhere([
                'regularElasticSchemaId' => (int) $this->regularElasticSchemaId,
                'xeoElasticSchemaId' => (int) $this->xeoElasticSchemaId,
            ])
            ->exists();
        if ($associationExists === true) {
            $validationResult->addError('This association already exists.');
        }
        return $validationResult;
    }

    protected function getRawLabels(): array
    {
        return [
            'regularElasticSchemaId' => 'Regular schema',
            'xeoElasticSchemaId' => 'Xeo schema',
            'mapping' => 'Mapping',
        ];
    }

    protected function getRawHints(): array
    {
        return [
            'regularElasticSchemaId' => 'Source elastic schema (block, page or common)',
            'xeoElasticSchemaId' => 'Target Xeo elastic schema (structured data)',
            'mapping' => 'JSON field mapping (e.g. {"title": "name", "description": "description"})',
        ];
    }
}
