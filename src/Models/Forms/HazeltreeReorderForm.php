<?php

declare(strict_types=1);

/**
 * HazeltreeReorderForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\Dboard\Enums\TreePosition;
use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\In;
use Yiisoft\Validator\Rule\Required;

/**
 * Form model for Hazeltree drag-and-drop reordering.
 * Used to validate sourceId, targetId and position parameters.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class HazeltreeReorderForm extends BridgeFormModel
{
    /**
     * @var string The source element ID
     */
    private string $sourceId = '';

    /**
     * @var string The target element ID
     */
    private string $targetId = '';

    /**
     * @var string The position relative to target (before, into, after)
     */
    private string $position = '';

    /**
     * Gets the source element ID.
     *
     * @return string The source ID
     */
    #[Bridge]
    public function getSourceId(): string
    {
        return $this->sourceId;
    }

    /**
     * Sets the source element ID.
     *
     * @param string $sourceId The source ID
     */
    #[Bridge]
    public function setSourceId(string $sourceId): void
    {
        $this->sourceId = $sourceId;
    }

    /**
     * Gets the target element ID.
     *
     * @return string The target ID
     */
    #[Bridge]
    public function getTargetId(): string
    {
        return $this->targetId;
    }

    /**
     * Sets the target element ID.
     *
     * @param string $targetId The target ID
     */
    #[Bridge]
    public function setTargetId(string $targetId): void
    {
        $this->targetId = $targetId;
    }

    /**
     * Gets the position relative to target.
     *
     * @return string The position
     */
    #[Bridge]
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * Sets the position relative to target.
     *
     * @param string $position The position
     */
    #[Bridge]
    public function setPosition(string $position): void
    {
        $this->position = $position;
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            'sourceId' => [
                new Required(),
            ],
            'targetId' => [
                new Required(),
            ],
            'position' => [
                new Required(),
                new In([
                    TreePosition::Before->value,
                    TreePosition::Into->value,
                    TreePosition::After->value,
                ]),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        return [
            'default' => ['sourceId', 'targetId', 'position'],
        ];
    }
}
