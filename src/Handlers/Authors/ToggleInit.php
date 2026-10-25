<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Authors;

use Blackcube\Dcore\Models\Author;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Enums\UiColor;

/**
 * Author toggle init action (GET).
 * Displays toggle confirmation modal.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ToggleInit extends AbstractPanel
{
    protected function getType(): DialogType { return DialogType::Modal; }
    protected function getModelClass(): string { return Author::class; }
    protected function getTitle(): string { return $this->translator->translate('Modify author', category: 'dboard-modules'); }
    protected function getMessage(): string
    {
        return $this->translator->translate('Author "{name}" will be {status, select, enabled{enabled} other{disabled}}.', [
            'name' => $this->getModelName(),
            'status' => $this->models['main']->isActive() === true ? 'disabled' : 'enabled',
        ], 'dboard-modules');
    }
    protected function getContentView(): string { return 'Commons/_toggle-content'; }
    protected function getColor(): UiColor { return UiColor::Warning; }
    protected function getModelName(): string
    {
        return $this->models['main']->getFirstname() . ' ' . $this->models['main']->getLastname();
    }
}
