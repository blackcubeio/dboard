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
 * ContentTag pivot form model.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ContentTagForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-content';

    protected ?int $contentId = null;
    protected ?int $tagId = null;

    protected bool $selected = false;

    protected string $tagName = '';
    protected int $tagLevel = 0;
    protected bool $tagActive = true;

    protected string $contentName = '';

    public function load(mixed $submittedValues, ?string $scope = null): bool
    {
        $this->selected = false;
        return parent::load($submittedValues, $scope);
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

    public function setSelected(bool $selected): void
    {
        $this->selected = $selected;
    }

    public function isSelected(): bool
    {
        return $this->selected;
    }

    #[Bridge]
    public function setTagName(string $tagName): void
    {
        $this->tagName = $tagName;
    }

    #[Bridge]
    public function getTagName(): string
    {
        return $this->tagName;
    }

    #[Bridge]
    public function setTagLevel(int $tagLevel): void
    {
        $this->tagLevel = $tagLevel;
    }

    #[Bridge]
    public function getTagLevel(): int
    {
        return $this->tagLevel;
    }

    #[Bridge]
    public function setTagActive(bool $tagActive): void
    {
        $this->tagActive = $tagActive;
    }

    #[Bridge]
    public function isTagActive(): bool
    {
        return $this->tagActive;
    }

    #[Bridge]
    public function setContentName(string $contentName): void
    {
        $this->contentName = $contentName;
    }

    #[Bridge]
    public function getContentName(): string
    {
        return $this->contentName;
    }

    public function scenarios(): array
    {
        return [
            'tagging' => [
                'contentId',
                'tagId',
                'selected',
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'contentId' => [
                new Required(),
                new Integer(min: 1),
            ],
            'tagId' => [
                new Required(),
                new Integer(min: 1),
            ],
            'selected' => [
                new BooleanValue(),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'selected' => $this->tagName,
        ];
    }
}
