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
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

/**
 * GlobalXeo form model with elastic properties support.
 * Bridges active status + elastic properties from JSON schema.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class GlobalXeoForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-content';
    protected ?string $translateElasticCategory = 'dboard-content';

    protected string $name = '';
    protected bool $active = false;

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

    public function load(mixed $submittedValues, ?string $scope = null): bool
    {
        $this->active = false;
        return parent::load($submittedValues, $scope);
    }

    public function scenarios(): array
    {
        return [
            'elastic' => [
                'name',
                'active',
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
            'active' => [
                new BooleanValue(),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'name' => 'Name',
            'active' => 'Active',
        ];
    }
}
