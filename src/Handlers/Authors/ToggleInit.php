<?php

declare(strict_types=1);

/**
 * ToggleInit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Authors;

use Blackcube\Dcore\Models\Author;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Dboard\Enums\PanelType;
use Blackcube\Bleet\Enums\UiColor;

/**
 * Author toggle init action (GET).
 * Displays toggle confirmation modal.
 */
final class ToggleInit extends AbstractPanel
{
    protected function getType(): PanelType { return PanelType::Modal; }
    protected function getModelClass(): string { return Author::class; }
    protected function getTitle(): string { return $this->translator->translate('Modify author', category: 'dboard-modules'); }
    protected function getMessage(): string
    {
        $status = $this->models['main']->isActive()
            ? $this->translator->translate('deactivated', category: 'dboard-modules')
            : $this->translator->translate('activated', category: 'dboard-modules');
        return $this->translator->translate('Author "{name}" will be {status}.', ['name' => $this->getModelName(), 'status' => $status], 'dboard-modules');
    }
    protected function getContentView(): string { return 'Commons/_toggle-content'; }
    protected function getColor(): UiColor { return UiColor::Warning; }
    protected function getModelName(): string
    {
        return $this->models['main']->getFirstname() . ' ' . $this->models['main']->getLastname();
    }
}
