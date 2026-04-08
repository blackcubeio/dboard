<?php

declare(strict_types=1);

/**
 * SitemapForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\In;
use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\Rule\Required;

/**
 * Sitemap form model.
 */
final class SitemapForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-common';

    protected ?int $id = null;
    protected string $frequency = 'daily';
    protected float $priority = 0.5;
    protected bool $active = false;

    public function load(mixed $data, ?string $scope = null): bool
    {
        // Reset checkboxes before load (unchecked = not sent)
        $this->active = false;
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
    public function setFrequency(string $frequency): void
    {
        $this->frequency = $frequency;
    }

    #[Bridge]
    public function getFrequency(): string
    {
        return $this->frequency;
    }

    #[Bridge]
    public function setPriority(float $priority): void
    {
        $this->priority = $priority;
    }

    #[Bridge]
    public function getPriority(): float
    {
        return $this->priority;
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

    public function scenarios(): array
    {
        return [
            'create' => [
                'frequency',
                'priority',
                'active',
            ],
            'edit' => [
                'frequency',
                'priority',
                'active',
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'frequency' => [
                new Required(),
                new In(['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never']),
            ],
            'priority' => [
                new Required(),
                new Number(min: 0.0, max: 1.0),
            ],
            'active' => [
                new BooleanValue(),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'frequency' => 'Frequency',
            'priority' => 'Priority',
            'active' => 'Active',
        ];
    }

    protected function getRawHints(): array
    {
        return [
            'frequency' => 'Update frequency for sitemap',
            'priority' => 'Sitemap priority (0.0 to 1.0)',
            'active' => 'Include in sitemap',
        ];
    }

    public static function getFrequencyOptions(): array
    {
        return [
            'always' => 'Always',
            'hourly' => 'Hourly',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'yearly' => 'Yearly',
            'never' => 'Never',
        ];
    }
}