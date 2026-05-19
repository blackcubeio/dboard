<?php

declare(strict_types=1);

/**
 * TypeForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

/**
 * Type form model.
 */
final class TypeForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-modules';

    protected string $name = '';
    protected ?string $handler = null;
    protected bool $contentAllowed = false;
    protected bool $tagAllowed = false;

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
    public function setHandler(?string $handler): void
    {
        $this->handler = $handler;
    }
    #[Bridge]
    public function getHandler(): ?string
    {
        return $this->handler;
    }
    #[Bridge]
    public function setContentAllowed(bool $contentAllowed): void
    {
        $this->contentAllowed = $contentAllowed;
    }
    #[Bridge]
    public function isContentAllowed(): bool
    {
        return $this->contentAllowed;
    }
    #[Bridge]
    public function setTagAllowed(bool $tagAllowed): void
    {
        $this->tagAllowed = $tagAllowed;
    }
    #[Bridge]
    public function isTagAllowed(): bool
    {
        return $this->tagAllowed;
    }

    public function load(mixed $data, ?string $scope = null): bool
    {
        $this->contentAllowed = false;
        $this->tagAllowed = false;
        return parent::load($data, $scope);
    }

    public function scenarios(): array
    {
        return [
            'create' => ['name', 'handler', 'contentAllowed', 'tagAllowed'],
            'edit' => ['name', 'handler', 'contentAllowed', 'tagAllowed'],
        ];
    }

    public function rules(): array
    {
        return [
            'name' => [
                new Required(),
                new Length(max: 255),
            ],
            'handler' => [
                new Length(max: 255),
            ],
            'contentAllowed' => [
                new BooleanValue(),
            ],
            'tagAllowed' => [
                new BooleanValue(),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'name' => 'Name',
            'handler' => 'Handler',
            'contentAllowed' => 'Content allowed',
            'tagAllowed' => 'Tag allowed',
        ];
    }

    protected function getRawHints(): array
    {
        return [
            'name' => 'Type name',
            'handler' => 'Associated SSR handler (optional)',
            'contentAllowed' => 'Allow this type for contents',
            'tagAllowed' => 'Allow this type for tags',
        ];
    }
}
