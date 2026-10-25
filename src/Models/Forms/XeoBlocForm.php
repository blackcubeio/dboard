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
 * Xeo bloc form model with elastic properties support.
 * Handles edit operations only (no add/delete/reorder).
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class XeoBlocForm extends BridgeFormModel
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
    public function load(mixed $submittedValues, ?string $scope = null): bool
    {
        $this->xeoBlocActive = false;
        return parent::load($submittedValues, $scope);
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
            'edit' => [
                'xeoBlocActive',
                self::ALL_ELASTIC_ATTRIBUTES,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            'xeoBlocActive' => [
                new BooleanValue(),
            ],
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
