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
use Blackcube\BridgeFormModel\Attributes\IntOrNull;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\In;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\StringValue;

/**
 * LlmMenu form model.
 *
 * Tree structure: Root (level 1) → Category (level 2) → Data (level 3).
 * contentId/tagId link the node to a page at any level: dcore's LlmsService renders
 * the link under the heading of a root or a category, and as the entry itself for data nodes.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class LlmMenuForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-content';

    protected ?int $id = null;
    protected string $name = '';
    protected ?string $description = null;
    #[IntOrNull]
    protected ?int $contentId = null;
    #[IntOrNull]
    protected ?int $tagId = null;

    protected bool $move = false;
    protected string $moveMode = TreePosition::Into->value;
    protected ?int $moveTargetId = null;

    protected int $expectedLevel = 3;

    public function load(mixed $submittedValues, ?string $scope = null): bool
    {
        $this->name = '';
        $this->description = null;
        $this->contentId = null;
        $this->tagId = null;
        $this->move = false;
        return parent::load($submittedValues, $scope);
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
            'create' => [
                'name',
                'description',
                'contentId',
                'tagId',
                'move',
                'moveMode',
                'moveTargetId',
            ],
            'edit' => [
                'name',
                'description',
                'contentId',
                'tagId',
                'move',
                'moveMode',
                'moveTargetId',
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'name' => [
                new StringValue(skipOnEmpty: true),
                new Length(max: 255),
            ],
            'description' => [
                new StringValue(skipOnEmpty: true),
            ],
            'contentId' => [
                new Integer(skipOnEmpty: true),
            ],
            'tagId' => [
                new Integer(skipOnEmpty: true),
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
