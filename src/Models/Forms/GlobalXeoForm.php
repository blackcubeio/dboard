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

    protected bool $active = false;

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
                'active',
                self::ALL_ELASTIC_ATTRIBUTES,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'active' => [
                new BooleanValue(),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'active' => 'Active',
        ];
    }
}
