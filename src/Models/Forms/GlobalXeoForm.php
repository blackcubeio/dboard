<?php

declare(strict_types=1);

/**
 * GlobalXeoForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\BooleanValue;

/**
 * GlobalXeo form model with elastic properties support.
 * Bridges active status + elastic properties from JSON schema.
 */
final class GlobalXeoForm extends BridgeFormModel
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

    public function load(mixed $data, ?string $scope = null): bool
    {
        $this->active = false;
        return parent::load($data, $scope);
    }

    public function scenarios(): array
    {
        return [
            'elastic' => ['active', self::ALL_ELASTIC_ATTRIBUTES],
        ];
    }

    public function rules(): array
    {
        return [
            'active' => [new BooleanValue()],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'active' => 'Active',
        ];
    }
}
