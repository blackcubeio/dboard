<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Languages;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Models\Menu;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Yiisoft\Router\CurrentRoute;

/**
 * Language delete init action (GET).
 * Displays delete confirmation modal, or error if language is used.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class DeleteInit extends AbstractPanel
{
    protected function getType(): DialogType { return DialogType::Modal; }
    protected function getModelClass(): string { return Language::class; }
    protected function getTitle(): string { return $this->translator->translate('Delete language', category: 'dboard-modules'); }
    protected function getContentView(): string { return 'Commons/_delete-content'; }
    protected function getMessage(): string { return $this->translator->translate('Language "{name}" will be permanently deleted.', [
        'name' => $this->getModelName(),
    ], 'dboard-modules'); }
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
            'contents' => Content::query()->andWhere([
                'languageId' => $languageId,
            ])->count(),
            'menus' => Menu::query()->andWhere([
                'languageId' => $languageId,
            ])->count(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $model = $this->models['main'];
        $usage = $this->getUsageCounts();

        if ($usage['contents'] > 0 || $usage['menus'] > 0) {
            $message = $this->translator->translate('Language "{name}" cannot be deleted because it is used by {contents, plural, =0{no content} one{# content} other{# contents}} and {menus, plural, =0{no menu} one{# menu} other{# menus}}.', [
                'name' => $model->getName(),
                'contents' => $usage['contents'],
                'menus' => $usage['menus'],
            ], 'dboard-modules');

            $content = (string) $this->renderPartial('Languages/_delete-error-content', [
                'message' => $message,
            ])->getBody();

            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Keep),
                    ...AureliaCommunication::dialogContent(Bleet::dialogFrame(DialogType::Modal)
                        ->title($this->translator->translate('Deletion not possible', category: 'dboard-modules'))
                        ->danger()
                        ->content($content)
                        ->render()),
                ],
            ];
        } else {
            $outputData = parent::prepareOutputData();
        }

        return $outputData;
    }
}
