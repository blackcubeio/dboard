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
 * Author delete init action (GET).
 * Displays delete confirmation modal.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class DeleteInit extends AbstractPanel
{
    protected function getType(): DialogType { return DialogType::Modal; }
    protected function getModelClass(): string { return Author::class; }
    protected function getTitle(): string { return $this->translator->translate('Delete author', category: 'dboard-modules'); }
    protected function getMessage(): string { return $this->translator->translate('Author "{name}" will be permanently deleted.', [
        'name' => $this->getModelName(),
    ], 'dboard-modules'); }
    protected function getContentView(): string { return 'Commons/_delete-content'; }
    protected function getColor(): UiColor { return UiColor::Danger; }
    protected function getModelName(): string
    {
        return $this->models['main']->getFirstname() . ' ' . $this->models['main']->getLastname();
    }
}
