<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Tags;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Enums\UiColor;

/**
 * Tag toggle init action (GET).
 * Displays toggle confirmation modal.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ToggleInit extends AbstractPanel
{
    protected function getType(): DialogType { return DialogType::Modal; }
    protected function getModelClass(): string { return Tag::class; }
    protected function getTitle(): string { return $this->translator->translate('Modify tag', category: 'dboard-content'); }
    protected function getContentView(): string { return 'Commons/_toggle-content'; }
    protected function getMessage(): string
    {
        return $this->translator->translate('Tag "{name}" will be {status, select, enabled{enabled} other{disabled}}.', [
            'name' => $this->getModelName(),
            'status' => $this->models['main']->isActive() === true ? 'disabled' : 'enabled',
        ], 'dboard-content');
    }
    protected function getColor(): UiColor { return UiColor::Warning; }
}
