<?php

declare(strict_types=1);

/**
 * FilterNodeForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeModel\Attributes\IntOrNull;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\Integer;

/**
 * Filter form model for node filtering and display mode on index pages.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class FilterNodeForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-common';

    #[IntOrNull]
    protected ?int $nodeId = null;

    protected bool $modeFlat = false;

    public function setNodeId(?int $nodeId): void
    {
        $this->nodeId = $nodeId;
    }

    public function getNodeId(): ?int
    {
        return $this->nodeId;
    }

    public function setModeFlat(bool $modeFlat): void
    {
        $this->modeFlat = $modeFlat;
    }

    public function isModeFlat(): bool
    {
        return $this->modeFlat;
    }

    public function load(mixed $data, ?string $scope = null): bool
    {
        $this->nodeId = null;
        $this->modeFlat = false;

        return parent::load($data, $scope);
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        return [
            'default' => ['nodeId', 'modeFlat'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            'nodeId' => [
                new Integer(min: 1, skipOnEmpty: true),
            ],
            'modeFlat' => [
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
            'nodeId' => 'Node',
            'modeFlat' => 'Recent',
        ];
    }
}
