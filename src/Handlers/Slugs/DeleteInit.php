<?php

declare(strict_types=1);

/**
 * DeleteInit.php
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
 * Slug delete init action (GET).
 * Displays delete confirmation modal.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class DeleteInit extends AbstractPanel
{
    protected function getType(): PanelType { return PanelType::Modal; }
    protected function getModelClass(): string { return Slug::class; }
    protected function getTitle(): string { return $this->translator->translate('Delete slug', category: 'dboard-modules'); }
    protected function getContentView(): string { return 'Commons/_delete-content'; }
    protected function getMessage(): string { return $this->translator->translate('Slug "{name}" will be permanently deleted.', ['name' => $this->getModelName()], 'dboard-modules'); }
    protected function getColor(): UiColor { return UiColor::Danger; }

    protected function getModelName(): string
    {
        return $this->models['main']->getPath();
    }
}
