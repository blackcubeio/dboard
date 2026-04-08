<?php

declare(strict_types=1);

/**
 * DeleteInit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Parameters;

use Blackcube\Dcore\Models\Parameter;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Dboard\Enums\PanelType;
use Blackcube\Bleet\Enums\UiColor;

/**
 * Parameter delete init action (GET).
 * Displays delete confirmation modal.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class DeleteInit extends AbstractPanel
{
    protected function getType(): PanelType { return PanelType::Modal; }
    protected function getModelClass(): string { return Parameter::class; }
    protected function getTitle(): string { return $this->translator->translate('Delete parameter', category: 'dboard-modules'); }
    protected function getMessage(): string { return $this->translator->translate('Parameter "{name}" will be permanently deleted.', ['name' => $this->getModelName()], 'dboard-modules'); }
    protected function getContentView(): string { return 'Commons/_delete-content'; }
    protected function getColor(): UiColor { return UiColor::Danger; }

    protected function primaryKeys(): array
    {
        return ['domain', 'name'];
    }

    protected function getModelName(): string
    {
        $model = $this->models['main'];
        return $model->getDomain() . '::' . $model->getName();
    }
}
