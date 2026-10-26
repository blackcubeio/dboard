<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Hosts;

use Blackcube\Dcore\Models\Host;
use Blackcube\Dcore\Models\Menu;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Handler\OutputType;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Yiisoft\Router\CurrentRoute;

/**
 * Host delete init action (GET).
 * Displays delete confirmation modal, or error if host is protected/used.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class DeleteInit extends AbstractPanel
{
    protected function getType(): DialogType { return DialogType::Modal; }
    protected function getModelClass(): string { return Host::class; }
    protected function getTitle(): string { return $this->translator->translate('Delete host', category: 'dboard-modules'); }
    protected function getContentView(): string { return 'Commons/_delete-content'; }
    protected function getMessage(): string { return $this->translator->translate('Host "{name}" will be permanently deleted.', [
        'name' => $this->getModelName(),
    ], 'dboard-modules'); }
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
            'slugs' => Slug::query()->andWhere([
                'hostId' => $hostId,
            ])->count(),
            'menus' => Menu::query()->andWhere([
                'hostId' => $hostId,
            ])->count(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $model = $this->models['main'];

        if ($model->getId() === 1) {
            $content = (string) $this->renderPartial('Hosts/_delete-error-content', [
                'message' => $this->translator->translate('The default host cannot be deleted.', category: 'dboard-modules'),
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
            $usage = $this->getUsageCounts();
            if ($usage['slugs'] > 0 || $usage['menus'] > 0) {
                $message = $this->translator->translate('Host "{name}" cannot be deleted because it is used by {slugs, plural, =0{no slug} one{# slug} other{# slugs}} and {menus, plural, =0{no menu} one{# menu} other{# menus}}.', [
                    'name' => $model->getName(),
                    'slugs' => $usage['slugs'],
                    'menus' => $usage['menus'],
                ], 'dboard-modules');

                $content = (string) $this->renderPartial('Hosts/_delete-error-content', [
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
        }

        return $outputData;
    }
}
