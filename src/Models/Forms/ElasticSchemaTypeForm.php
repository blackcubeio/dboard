<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeFormModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Required;

/**
 * ElasticSchemaType pivot form model.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ElasticSchemaTypeForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-modules';

    protected ?int $typeId = null;
    protected ?int $elasticSchemaId = null;

    protected bool $allowed = false;

    protected string $typeName = '';

    public function load(mixed $submittedValues, ?string $scope = null): bool
    {
        $this->allowed = false;
        return parent::load($submittedValues, $scope);
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
    public function setTypeName(string $typeName): void
    {
        $this->typeName = $typeName;
    }

    #[Bridge]
    public function getTypeName(): string
    {
        return $this->typeName;
    }

    public function scenarios(): array
    {
        return [
            'edit' => [
                'typeId',
                'elasticSchemaId',
                'allowed',
            ],
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
            'allowed' => $this->typeName,
        ];
    }
}
