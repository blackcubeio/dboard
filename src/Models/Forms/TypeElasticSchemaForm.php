<?php

declare(strict_types=1);

/**
 * TypeElasticSchemaForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Required;

/**
 * TypeElasticSchema pivot form model.
 */
final class TypeElasticSchemaForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-modules';

    // TypeElasticSchema - pivot table elements
    protected ?int $typeId = null;
    protected ?int $elasticSchemaId = null;

    // Form element (form-only)
    protected bool $allowed = false;

    // ElasticSchema - display elements
    protected string $elasticSchemaName = '';

    public function load(mixed $data, ?string $scope = null): bool
    {
        $this->allowed = false;
        return parent::load($data, $scope);
    }

    #[Bridge]
    public function setTypeId(?int $typeId): void
    {
        $this->typeId = $typeId;
    }

    #[Bridge]
    public function getTypeId(): ?int
    {
        return $this->typeId;
    }

    #[Bridge]
    public function setElasticSchemaId(?int $elasticSchemaId): void
    {
        $this->elasticSchemaId = $elasticSchemaId;
    }

    #[Bridge]
    public function getElasticSchemaId(): ?int
    {
        return $this->elasticSchemaId;
    }

    public function setAllowed(bool $allowed): void
    {
        $this->allowed = $allowed;
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    #[Bridge]
    public function setElasticSchemaName(string $elasticSchemaName): void
    {
        $this->elasticSchemaName = $elasticSchemaName;
    }

    #[Bridge]
    public function getElasticSchemaName(): string
    {
        return $this->elasticSchemaName;
    }

    public function scenarios(): array
    {
        return [
            'edit' => ['typeId', 'elasticSchemaId', 'allowed'],
        ];
    }

    public function rules(): array
    {
        return [
            'typeId' => [
                new Required(),
                new Integer(min: 1),
            ],
            'elasticSchemaId' => [
                new Required(),
                new Integer(min: 1),
            ],
            'allowed' => [
                new BooleanValue(),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'allowed' => $this->elasticSchemaName,
        ];
    }
}
