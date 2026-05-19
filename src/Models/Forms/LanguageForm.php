<?php

declare(strict_types=1);

/**
 * LanguageForm.php
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
 * Language form model.
 */
final class LanguageForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-modules';

    protected string $id = '';
    protected string $name = '';
    protected bool $active = false;
    protected bool $main = false;

    #[Bridge]
    public function setId(string $id): void
    {
        $this->id = $id;
    }
    #[Bridge]
    public function getId(): string
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
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
    #[Bridge]
    public function isActive(): bool
    {
        return $this->active;
    }
    #[Bridge]
    public function setMain(bool $main): void
    {
        $this->main = $main;
    }
    #[Bridge]
    public function isMain(): bool
    {
        return $this->main;
    }

    public function load(mixed $data, ?string $scope = null): bool
    {
        // Reset checkboxes before load (unchecked = not sent)
        $this->active = false;
        $this->main = false;
        return parent::load($data, $scope);
    }

    public function scenarios(): array
    {
        return [
            'create' => ['id', 'name', 'active', 'main'],
            'edit' => ['id', 'name', 'active', 'main'],
        ];
    }

    public function rules(): array
    {
        return [
            'id' => [
                new Required(),
                new Length(max: 8),
            ],
            'name' => [
                new Required(),
                new Length(max: 255),
            ],
            'active' => [
                new BooleanValue(),
            ],
            'main' => [
                new BooleanValue(),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'active' => 'Active',
            'main' => 'Main',
        ];
    }

    protected function getRawHints(): array
    {
        return [
            'active' => 'Language status',
        ];
    }
}
