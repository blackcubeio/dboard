<?php

declare(strict_types=1);

/**
 * BlocForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\Dboard\Enums\ReorderMode;
use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\Each;
use Yiisoft\Validator\Rule\In;
use Yiisoft\Validator\Rule\Integer;

/**
 * Bloc form model with elastic properties support.
 * Handles edit, add, move and dnd (drag-n-drop) operations.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class BlocForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-common';
    protected ?string $translateElasticCategory = 'dboard-content';

    /**
     * @var int|null Bloc ID
     */
    protected ?int $id = null;

    /**
     * @var bool Bloc active status
     */
    protected bool $active = false;

    /**
     * @var int|null Elastic schema ID (for add scenario)
     */
    protected ?int $elasticSchemaId = null;

    /**
     * @var int|null Bloc ID to add after (for add scenario, form-only)
     */
    protected ?int $blocAdd = null;

    /**
     * @var int|null Bloc ID to move (for move scenario, form-only)
     */
    protected ?int $blocId = null;

    /**
     * @var string|null Move mode (for move scenario, form-only)
     */
    protected ?string $mode = null;

    /**
     * @var array<int, int>|null Bloc order (for dnd scenario, form-only)
     */
    protected ?array $order = null;

    /**
     * @var array<string, string> Elastic labels
     */
    private array $elasticLabels = [];

    /**
     * @var array<string, string> Elastic hints
     */
    private array $elasticHints = [];

    /**
     * Sets the bloc ID.
     *
     * @param int|null $id The bloc ID
     * @return void
     */
    #[Bridge]
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * Gets the bloc ID.
     *
     * @return int|null The bloc ID
     */
    #[Bridge]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Sets the active status.
     *
     * @param bool $active The active status
     * @return void
     */
    #[Bridge]
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * Gets the active status.
     *
     * @return bool The active status
     */
    #[Bridge]
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Sets the elastic schema ID.
     *
     * @param int|null $elasticSchemaId The elastic schema ID
     * @return void
     */
    #[Bridge]
    public function setElasticSchemaId(?int $elasticSchemaId): void
    {
        $this->elasticSchemaId = $elasticSchemaId;
    }

    /**
     * Gets the elastic schema ID.
     *
     * @return int|null The elastic schema ID
     */
    #[Bridge]
    public function getElasticSchemaId(): ?int
    {
        return $this->elasticSchemaId;
    }

    /**
     * Sets the bloc ID to add after.
     *
     * @param int|null $blocAdd The bloc ID to add after
     * @return void
     */
    public function setBlocAdd(?int $blocAdd): void
    {
        $this->blocAdd = $blocAdd;
    }

    /**
     * Gets the bloc ID to add after.
     *
     * @return int|null The bloc ID to add after
     */
    public function getBlocAdd(): ?int
    {
        return $this->blocAdd;
    }

    /**
     * Sets the bloc ID to move.
     *
     * @param int|null $blocId The bloc ID to move
     * @return void
     */
    public function setBlocId(?int $blocId): void
    {
        $this->blocId = $blocId;
    }

    /**
     * Gets the bloc ID to move.
     *
     * @return int|null The bloc ID to move
     */
    public function getBlocId(): ?int
    {
        return $this->blocId;
    }

    /**
     * Sets the move mode.
     *
     * @param string|null $mode The move mode
     * @return void
     */
    public function setMode(?string $mode): void
    {
        $this->mode = $mode;
    }

    /**
     * Gets the move mode.
     *
     * @return string|null The move mode
     */
    public function getMode(): ?string
    {
        return $this->mode;
    }

    /**
     * Sets the bloc order.
     *
     * @param array<int, int>|null $order The bloc order
     * @return void
     */
    public function setOrder(?array $order): void
    {
        $this->order = $order;
    }

    /**
     * Gets the bloc order.
     *
     * @return array<int, int>|null The bloc order
     */
    public function getOrder(): ?array
    {
        return $this->order;
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        return [
            'edit' => ['active', self::ALL_ELASTIC_ATTRIBUTES],
            'add' => ['elasticSchemaId', 'blocAdd'],
            'move' => ['blocId', 'mode'],
            'dnd' => ['order'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            'active' => [new BooleanValue()],
            'elasticSchemaId' => [new Integer(min: 1)],
            'blocAdd' => [new Integer(min: 1, skipOnEmpty: true)],
            'blocId' => [new Integer(min: 1)],
            'mode' => [new In([ReorderMode::MoveUp->value, ReorderMode::MoveDown->value], skipOnEmpty: true)],
            'order' => [new Each([new Integer(min: 1)], skipOnEmpty: true)],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRawLabels(): array
    {
        return [
            'id' => 'ID',
            'active' => 'Active',
            'elasticSchemaId' => 'Block type',
            'blocAdd' => 'After block',
            'blocId' => 'Block',
            'mode' => 'Direction',
            'order' => 'Order',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRawHints(): array
    {
        return [
            'active' => 'Block status',
        ];
    }
}
