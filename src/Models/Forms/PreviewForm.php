<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

/**
 * Preview form model.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class PreviewForm extends BridgeFormModel
{
    protected ?string $simulateDate = null;

    public function setSimulateDate(?string $simulateDate): void
    {
        $this->simulateDate = $simulateDate;
    }

    public function getSimulateDate(): ?string
    {
        return $this->simulateDate;
    }

    protected function getRawLabels(): array
    {
        return [
            'simulateDate' => 'Simulation date',
        ];
    }
}
