<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Slugs;

use Blackcube\Dcore\Models\Slug;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Enums\UiColor;

/**
 * Slug delete init action (GET).
 * Displays delete confirmation modal.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class DeleteInit extends AbstractPanel
{
    protected function getType(): DialogType { return DialogType::Modal; }
    protected function getModelClass(): string { return Slug::class; }
    protected function getTitle(): string { return $this->translator->translate('Delete slug', category: 'dboard-modules'); }
    protected function getContentView(): string { return 'Commons/_delete-content'; }
    protected function getMessage(): string { return $this->translator->translate('Slug "{name}" will be permanently deleted.', [
        'name' => $this->getModelName(),
    ], 'dboard-modules'); }
    protected function getColor(): UiColor { return UiColor::Danger; }

    protected function getModelName(): string
    {
        return $this->models['main']->getPath();
    }
}
