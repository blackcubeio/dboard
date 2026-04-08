<?php

declare(strict_types=1);

/**
 * TagForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\In;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

/**
 * Tag form model.
 */
final class TagForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-content';
    protected ?string $translateElasticCategory = 'dboard-content';

    protected ?int $id = null;
    protected string $name = '';
    protected ?string $languageId = null;
    protected ?int $typeId = null;
    protected ?int $elasticSchemaId = null;
    protected bool $active = false;

    // Hazeltree positioning (form-only)
    protected bool $move = false;
    protected string $moveMode = 'into'; // 'into', 'before', 'after'
    protected ?int $moveTargetId = null;

    public function load(mixed $data, ?string $scope = null): bool
    {
        // Reset checkboxes before load (unchecked = not sent)
        $this->active = false;
        $this->move = false;
        $result = parent::load($data, $scope);
        // Normalize optional FKs (0 → null)
        if ($this->typeId === 0) {
            $this->typeId = null;
        }
        if ($this->elasticSchemaId === 0) {
            $this->elasticSchemaId = null;
        }
        return $result;
    }

    #[Bridge]
    public function setId(?int $id): void
    {
        $this->id = $id;
    }
    #[Bridge]
    public function getId(): ?int
    {
        return $this->id;
    }
    #[Bridge]
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    #[Bridge]
    public function getName(): string
    {
        return $this->name;
    }
    #[Bridge]
    public function setLanguageId(?string $languageId): void
    {
        $this->languageId = $languageId;
    }
    #[Bridge]
    public function getLanguageId(): ?string
    {
        return $this->languageId;
    }
    #[Bridge]
    public function setTypeId(?int $typeId): void
    {
        // 0 or null → null (no type)
        $this->typeId = $typeId ?: null;
    }
    #[Bridge]
    public function getTypeId(): ?int
    {
        return $this->typeId;
    }
    #[Bridge]
    public function setElasticSchemaId(?int $elasticSchemaId): void
    {
        // 0 or null → null (no schema)
        $this->elasticSchemaId = $elasticSchemaId ?: null;
    }
    #[Bridge]
    public function getElasticSchemaId(): ?int
    {
        return $this->elasticSchemaId;
    }
    #[Bridge]
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
    #[Bridge]
    public function isActive(): bool
    {
        return $this->active;
    }
    public function setMove(bool $move): void
    {
        $this->move = $move;
    }
    public function isMove(): bool
    {
        return $this->move;
    }
    public function setMoveMode(string $moveMode): void
    {
        $this->moveMode = $moveMode;
    }
    public function getMoveMode(): string
    {
        return $this->moveMode;
    }
    public function setMoveTargetId(?int $moveTargetId): void
    {
        $this->moveTargetId = $moveTargetId;
    }
    public function getMoveTargetId(): ?int
    {
        return $this->moveTargetId;
    }

    public function scenarios(): array
    {
        return [
            'create' => [
                'name', 'languageId', 'typeId', 'elasticSchemaId', 'active', 'move', 'moveMode', 'moveTargetId',
            ],
            'edit' => [
                'name', 'languageId', 'typeId', 'elasticSchemaId', 'active', 'move', 'moveMode', 'moveTargetId',
            ],
            'elastic' => [
                self::ALL_ELASTIC_ATTRIBUTES,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'name' => [
                new Required(),
                new Length(max: 255),
            ],
            'languageId' => [
                new Required(),
            ],
            'typeId' => [
                new Integer(skipOnEmpty: true),
            ],
            'elasticSchemaId' => [
                new Integer(skipOnEmpty: true),
            ],
            'active' => [
                new BooleanValue(),
            ],
            'move' => [
                new BooleanValue(skipOnEmpty: true),
            ],
            'moveMode' => [
                new In(['into', 'before', 'after']),
            ],
            'moveTargetId' => [
                new Integer(skipOnEmpty: true),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'languageId' => 'Language',
            'typeId' => 'Type',
            'elasticSchemaId' => 'Properties',
            'active' => 'Active',
            'move' => 'Move',
            'moveMode' => 'Mode',
            'moveTargetId' => 'Target',
        ];
    }

    protected function getRawHints(): array
    {
        return [
            'active' => 'Tag status',
            'move' => 'Check to position the tag',
            'typeId' => 'Optional (to make routable or attach blocs)',
            'elasticSchemaId' => 'Schema for dynamic properties',
        ];
    }
}
