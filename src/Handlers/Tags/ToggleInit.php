<?php

declare(strict_types=1);

/**
 * ToggleInit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Tags;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Dboard\Enums\PanelType;
use Blackcube\Bleet\Enums\UiColor;

/**
 * Tag toggle init action (GET).
 * Displays toggle confirmation modal.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class ToggleInit extends AbstractPanel
{
    protected function getType(): PanelType { return PanelType::Modal; }
    protected function getModelClass(): string { return Tag::class; }
    protected function getTitle(): string { return $this->translator->translate('Modify tag', category: 'dboard-content'); }
    protected function getContentView(): string { return 'Commons/_toggle-content'; }
    protected function getMessage(): string
    {
        $status = $this->models['main']->isActive()
            ? $this->translator->translate('disabled', category: 'dboard-content')
            : $this->translator->translate('enabled', category: 'dboard-content');
        return $this->translator->translate('Tag "{name}" will be {status}.', ['name' => $this->getModelName(), 'status' => $status], 'dboard-content');
    }
    protected function getColor(): UiColor { return UiColor::Warning; }
}
