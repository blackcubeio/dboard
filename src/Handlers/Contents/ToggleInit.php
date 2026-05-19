<?php

declare(strict_types=1);

/**
 * ToggleInit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Contents;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Dboard\Enums\PanelType;
use Blackcube\Bleet\Enums\UiColor;

/**
 * Content toggle init action (GET).
 * Displays toggle confirmation modal.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class ToggleInit extends AbstractPanel
{
    protected function getType(): PanelType { return PanelType::Modal; }
    protected function getModelClass(): string { return Content::class; }
    protected function getTitle(): string { return $this->translator->translate('Modify content', category: 'dboard-content'); }
    protected function getContentView(): string { return 'Commons/_toggle-content'; }
    protected function getMessage(): string
    {
        $status = $this->models['main']->isActive()
            ? $this->translator->translate('disabled', category: 'dboard-content')
            : $this->translator->translate('enabled', category: 'dboard-content');
        return $this->translator->translate('Content "{name}" will be {status}.', ['name' => $this->getModelName(), 'status' => $status], 'dboard-content');
    }
    protected function getColor(): UiColor { return UiColor::Warning; }
}
