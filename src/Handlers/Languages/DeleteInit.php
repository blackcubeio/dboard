<?php

declare(strict_types=1);

/**
 * DeleteInit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Languages;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Models\Menu;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Enums\PanelType;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Yiisoft\Router\CurrentRoute;

/**
 * Language delete init action (GET).
 * Displays delete confirmation modal, or error if language is used.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class DeleteInit extends AbstractPanel
{
    protected function getType(): PanelType { return PanelType::Modal; }
    protected function getModelClass(): string { return Language::class; }
    protected function getTitle(): string { return $this->translator->translate('Delete language', category: 'dboard-modules'); }
    protected function getContentView(): string { return 'Commons/_delete-content'; }
    protected function getMessage(): string { return $this->translator->translate('Language "{name}" will be permanently deleted.', ['name' => $this->getModelName()], 'dboard-modules'); }
    protected function getColor(): UiColor { return UiColor::Danger; }

    /**
     * Check if language is used by Content or Menu.
     *
     * @return array{contents: int, menus: int} Usage counts
     */
    private function getUsageCounts(): array
    {
        $languageId = $this->models['main']->getId();
        return [
            'contents' => Content::query()->andWhere(['languageId' => $languageId])->count(),
            'menus' => Menu::query()->andWhere(['languageId' => $languageId])->count(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $model = $this->models['main'];
        $usage = $this->getUsageCounts();

        // Langue utilisée → afficher erreur
        if ($usage['contents'] > 0 || $usage['menus'] > 0) {
            $header = (string) $this->renderPartial('Commons/_modal-header', [
                'title' => $this->translator->translate('Deletion not possible', category: 'dboard-modules'),
                'uiColor' => UiColor::Danger,
            ])->getBody();

            $parts = [];
            if ($usage['contents'] > 0) {
                $parts[] = $usage['contents'] . ' content(s)';
            }
            if ($usage['menus'] > 0) {
                $parts[] = $usage['menus'] . ' menu(s)';
            }
            $message = $this->translator->translate('Language "{name}" cannot be deleted because it is used by {usage}.', ['name' => $model->getName(), 'usage' => implode(' and ', $parts)], 'dboard-modules');

            $content = (string) $this->renderPartial('Languages/_delete-error-content', [
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

        // Langue non utilisée → confirmation normale (parent)
        return parent::prepareOutputData();
    }
}
