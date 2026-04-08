<?php

declare(strict_types=1);

/**
 * DeleteInit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Hosts;

use Blackcube\Dcore\Models\Host;
use Blackcube\Dcore\Models\Menu;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Enums\PanelType;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Yiisoft\Router\CurrentRoute;

/**
 * Host delete init action (GET).
 * Displays delete confirmation modal, or error if host is protected/used.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class DeleteInit extends AbstractPanel
{
    protected function getType(): PanelType { return PanelType::Modal; }
    protected function getModelClass(): string { return Host::class; }
    protected function getTitle(): string { return $this->translator->translate('Delete host', category: 'dboard-modules'); }
    protected function getContentView(): string { return 'Commons/_delete-content'; }
    protected function getMessage(): string { return $this->translator->translate('Host "{name}" will be permanently deleted.', ['name' => $this->getModelName()], 'dboard-modules'); }
    protected function getColor(): UiColor { return UiColor::Danger; }

    /**
     * Check if host is used by Slug or Menu.
     *
     * @return array{slugs: int, menus: int} Usage counts
     */
    private function getUsageCounts(): array
    {
        $hostId = $this->models['main']->getId();
        return [
            'slugs' => Slug::query()->andWhere(['hostId' => $hostId])->count(),
            'menus' => Menu::query()->andWhere(['hostId' => $hostId])->count(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $model = $this->models['main'];

        // Host id=1 protected
        if ($model->getId() === 1) {
            $header = (string) $this->renderPartial('Commons/_modal-header', [
                'title' => $this->translator->translate('Deletion not possible', category: 'dboard-modules'),
                'uiColor' => UiColor::Danger,
            ])->getBody();

            $content = (string) $this->renderPartial('Hosts/_delete-error-content', [
                'message' => $this->translator->translate('The default host cannot be deleted.', category: 'dboard-modules'),
            ])->getBody();

            return [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Keep),
                    ...AureliaCommunication::dialogContent($header, $content, UiColor::Danger),
                ],
            ];
        }

        // Host in use
        $usage = $this->getUsageCounts();
        if ($usage['slugs'] > 0 || $usage['menus'] > 0) {
            $header = (string) $this->renderPartial('Commons/_modal-header', [
                'title' => $this->translator->translate('Deletion not possible', category: 'dboard-modules'),
                'uiColor' => UiColor::Danger,
            ])->getBody();

            $message = $this->translator->translate('Host "{name}" cannot be deleted because it is used by {slugs} slug(s) and {menus} menu(s).', ['name' => $model->getName(), 'slugs' => $usage['slugs'], 'menus' => $usage['menus']], 'dboard-modules');

            $content = (string) $this->renderPartial('Hosts/_delete-error-content', [
                'message' => $message,
            ])->getBody();

            return [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Keep),
                    ...AureliaCommunication::dialogContent($header, $content, UiColor::Danger),
                ],
            ];
        }

        // Host not protected and not in use → normal confirmation
        return parent::prepareOutputData();
    }
}
