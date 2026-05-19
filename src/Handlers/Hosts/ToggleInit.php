<?php

declare(strict_types=1);

/**
 * ToggleInit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Hosts;

use Blackcube\Dcore\Models\Host;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Enums\PanelType;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Yiisoft\Router\CurrentRoute;

/**
 * Host toggle init action (GET).
 * Displays toggle confirmation modal, or error if host is protected.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class ToggleInit extends AbstractPanel
{
    protected function getType(): PanelType { return PanelType::Modal; }
    protected function getModelClass(): string { return Host::class; }
    protected function getTitle(): string { return $this->translator->translate('Modify host', category: 'dboard-modules'); }
    protected function getContentView(): string { return 'Commons/_toggle-content'; }
    protected function getMessage(): string
    {
        $status = $this->models['main']->isActive()
            ? $this->translator->translate('disabled', category: 'dboard-modules')
            : $this->translator->translate('enabled', category: 'dboard-modules');
        return $this->translator->translate('Host "{name}" will be {status}.', ['name' => $this->getModelName(), 'status' => $status], 'dboard-modules');
    }
    protected function getColor(): UiColor { return UiColor::Warning; }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $model = $this->models['main'];

        // Host id=1 protected
        if ($model->getId() === 1) {
            $header = (string) $this->renderPartial('Commons/_modal-header', [
                'title' => $this->translator->translate('Modification not possible', category: 'dboard-modules'),
                'uiColor' => UiColor::Danger,
            ])->getBody();

            $content = (string) $this->renderPartial('Hosts/_toggle-error-content', [
                'message' => $this->translator->translate('The default host cannot be modified.', category: 'dboard-modules'),
            ])->getBody();

            return [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Keep),
                    ...AureliaCommunication::dialogContent($header, $content, UiColor::Danger),
                ],
            ];
        }

        // Host not protected → normal confirmation
        return parent::prepareOutputData();
    }
}
