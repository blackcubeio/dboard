<?php

declare(strict_types=1);

/**
 * LlmMenuForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeModel\Attributes\Bridge;
use Blackcube\BridgeModel\Attributes\IntOrNull;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\In;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\StringValue;
use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Result;

/**
 * LlmMenu form model.
 *
 * Tree structure: Root (level 1) → Category (level 2) → Data (level 3).
 * contentId/tagId are only relevant for Data nodes (level >= 3).
 */
final class LlmMenuForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-content';

    protected ?int $id = null;
    protected string $name = '';
    protected ?string $description = null;
    #[IntOrNull]
    protected ?int $contentId = null;
    #[IntOrNull]
    protected ?int $tagId = null;

    // Positioning (form-only)
    protected bool $move = false;
    protected string $moveMode = 'into'; // 'into', 'before', 'after'
    protected ?int $moveTargetId = null;

    // Level context (set by handler before validation, NOT bridged)
    protected int $expectedLevel = 3;

    public function load(mixed $data, ?string $scope = null): bool
    {
        // Reset checkboxes before load (unchecked = not sent)
        $this->move = false;
        return parent::load($data, $scope);
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
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
    #[Bridge]
    public function getDescription(): ?string
    {
        return $this->description;
    }
    #[Bridge]
    public function setContentId(?int $contentId): void
    {
        $this->contentId = $contentId;
    }
    #[Bridge]
    public function getContentId(): ?int
    {
        return $this->contentId;
    }
    #[Bridge]
    public function setTagId(?int $tagId): void
    {
        $this->tagId = $tagId;
    }
    #[Bridge]
    public function getTagId(): ?int
    {
        return $this->tagId;
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

    public function setExpectedLevel(int $expectedLevel): void
    {
        $this->expectedLevel = $expectedLevel;
    }

    public function getExpectedLevel(): int
    {
        return $this->expectedLevel;
    }

    public function scenarios(): array
    {
        return [
            'create' => ['name', 'description', 'contentId', 'tagId', 'move', 'moveMode', 'moveTargetId'],
            'edit' => ['name', 'description', 'contentId', 'tagId', 'move', 'moveMode', 'moveTargetId'],
        ];
    }

    public function rules(): array
    {
        return [
            'name' => [
                new Callback(callback: function (mixed $value): Result {
                    $result = new Result();
                    if ($this->expectedLevel < 3 && ($value === null || $value === '')) {
                        $result->addError($this->translator?->translate('Value cannot be blank.', category: 'yii-validator') ?? 'Value cannot be blank.');
                    }
                    return $result;
                }),
                new Length(max: 255),
            ],
            'description' => [
                new StringValue(),
            ],
            'contentId' => [
                new Integer(skipOnEmpty: true),
                new Callback(callback: function (mixed $value): Result {
                    $result = new Result();
                    if ($this->expectedLevel >= 3 && $this->contentId === null && $this->tagId === null) {
                        $result->addError($this->translator?->translate('Either content or tag must be selected.', category: 'dboard-content') ?? 'Either content or tag must be selected.');
                    }
                    return $result;
                }),
            ],
            'tagId' => [
                new Integer(skipOnEmpty: true),
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
            'description' => 'Description',
            'contentId' => 'Content',
            'tagId' => 'Tag',
            'move' => 'Move',
            'moveMode' => 'Mode',
            'moveTargetId' => 'Target',
        ];
    }

    protected function getRawHints(): array
    {
        return [
            'description' => 'Description of the LLM menu entry',
            'contentId' => 'Linked content',
            'tagId' => 'Linked tag',
            'move' => 'Check to position the LLM menu',
        ];
    }
}
