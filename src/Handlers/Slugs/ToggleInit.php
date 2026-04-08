<?php

declare(strict_types=1);

/**
 * ToggleInit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Slugs;

use Blackcube\Dcore\Models\Slug;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Dboard\Enums\PanelType;
use Blackcube\Bleet\Enums\UiColor;

/**
 * Slug toggle init action (GET).
 * Displays toggle confirmation modal.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class ToggleInit extends AbstractPanel
{
    protected function getType(): PanelType { return PanelType::Modal; }
    protected function getModelClass(): string { return Slug::class; }
    protected function getTitle(): string { return $this->translator->translate('Modify slug', category: 'dboard-modules'); }
    protected function getContentView(): string { return 'Commons/_toggle-content'; }
    protected function getMessage(): string
    {
        $status = $this->models['main']->isActive()
            ? $this->translator->translate('disabled', category: 'dboard-modules')
            : $this->translator->translate('enabled', category: 'dboard-modules');
        return $this->translator->translate('Slug "{name}" will be {status}.', ['name' => $this->getModelName(), 'status' => $status], 'dboard-modules');
    }
    protected function getColor(): UiColor { return UiColor::Warning; }

    protected function getModelName(): string
    {
        return $this->models['main']->getPath();
    }
}
