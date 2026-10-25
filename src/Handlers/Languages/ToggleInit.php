<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Languages;

use Blackcube\Dcore\Models\Language;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Enums\UiColor;

/**
 * Language toggle init action (GET).
 * Displays toggle confirmation modal.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ToggleInit extends AbstractPanel
{
    protected function getType(): DialogType { return DialogType::Modal; }
    protected function getModelClass(): string { return Language::class; }
    protected function getTitle(): string { return $this->translator->translate('Modify language', category: 'dboard-modules'); }
    protected function getContentView(): string { return 'Commons/_toggle-content'; }
    protected function getMessage(): string
    {
        return $this->translator->translate('Language "{name}" will be {status, select, enabled{enabled} other{disabled}}.', [
            'name' => $this->getModelName(),
            'status' => $this->models['main']->isActive() === true ? 'disabled' : 'enabled',
        ], 'dboard-modules');
    }
    protected function getColor(): UiColor { return UiColor::Warning; }
}
