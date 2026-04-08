<?php

declare(strict_types=1);

/**
 * XeoBlocForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\BooleanValue;

/**
 * Xeo bloc form model with elastic properties support.
 * Handles edit operations only (no add/delete/reorder).
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class XeoBlocForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-content';
    protected ?string $translateElasticCategory = 'dboard-content';

    /**
     * @var bool Bloc active status (named xeoBlocActive to avoid elastic collision)
     */
    protected bool $xeoBlocActive = false;

    /**
     * {@inheritdoc}
     */
    public function load(mixed $data, ?string $scope = null): bool
    {
        // Reset checkbox before load (unchecked = absent from POST)
        $this->xeoBlocActive = false;
        return parent::load($data, $scope);
    }

    /**
     * Sets the xeo bloc active status.
     *
     * @param bool $xeoBlocActive The active status
     * @return void
     */
    #[Bridge(getter: 'isActive')]
    public function setXeoBlocActive(bool $xeoBlocActive): void
    {
        $this->xeoBlocActive = $xeoBlocActive;
    }

    /**
     * Gets the xeo bloc active status.
     *
     * @return bool The active status
     */
    #[Bridge(setter: 'setActive')]
    public function isXeoBlocActive(): bool
    {
        return $this->xeoBlocActive;
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        return [
            'edit' => ['xeoBlocActive', self::ALL_ELASTIC_ATTRIBUTES],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            'xeoBlocActive' => [new BooleanValue()],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRawLabels(): array
    {
        return [
            'xeoBlocActive' => 'Active',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRawHints(): array
    {
        return [
            'xeoBlocActive' => 'Enable this structured data block',
        ];
    }
}
