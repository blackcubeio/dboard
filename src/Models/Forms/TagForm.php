<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\Dboard\Enums\TreePosition;
use Blackcube\BridgeFormModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\In;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

/**
 * Tag form model.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class TagForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-content';
    protected ?string $translateElasticCategory = 'dboard-content';

    protected ?int $id = null;
    protected string $name = '';
    protected ?string $languageId = null;
    protected ?int $typeId = null;
    protected ?int $elasticSchemaId = null;
    protected bool $active = false;

    protected bool $move = false;
    protected string $moveMode = TreePosition::Into->value;
    protected ?int $moveTargetId = null;

    public function load(mixed $submittedValues, ?string $scope = null): bool
    {
        $this->active = false;
        $this->move = false;
        $isLoaded = parent::load($submittedValues, $scope);
        if ($this->typeId === 0) {
            $this->typeId = null;
        }
        if ($this->elasticSchemaId === 0) {
            $this->elasticSchemaId = null;
        }
        return $isLoaded;
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
        $this->typeId = null;
        if (empty($typeId) === false) {
            $this->typeId = $typeId;
        }
    }
    #[Bridge]
    public function getTypeId(): ?int
    {
        return $this->typeId;
    }
    #[Bridge]
    public function setElasticSchemaId(?int $elasticSchemaId): void
    {
        $this->elasticSchemaId = null;
        if (empty($elasticSchemaId) === false) {
            $this->elasticSchemaId = $elasticSchemaId;
        }
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
                'name',
                'languageId',
                'typeId',
                'elasticSchemaId',
                'active',
                'move',
                'moveMode',
                'moveTargetId',
            ],
            'edit' => [
                'name',
                'languageId',
                'typeId',
                'elasticSchemaId',
                'active',
                'move',
                'moveMode',
                'moveTargetId',
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
                new In([
                    TreePosition::Before->value,
                    TreePosition::Into->value,
                    TreePosition::After->value,
                ]),
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
