<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Types;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dcore\Models\Type;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Handler\OutputType;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Yiisoft\Router\CurrentRoute;

/**
 * Type delete init action (GET).
 * Displays delete confirmation modal, or error if type is used.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class DeleteInit extends AbstractPanel
{
    protected function getType(): DialogType { return DialogType::Modal; }
    protected function getModelClass(): string { return Type::class; }
    protected function getTitle(): string { return $this->translator->translate('Delete type', category: 'dboard-modules'); }
    protected function getContentView(): string { return 'Commons/_delete-content'; }
    protected function getMessage(): string { return $this->translator->translate('Type "{name}" will be permanently deleted.', [
        'name' => $this->getModelName(),
    ], 'dboard-modules'); }
    protected function getColor(): UiColor { return UiColor::Danger; }

    /**
     * Check if type is used by Content or Tag.
     *
     * @return array{contents: int, tags: int} Usage counts
     */
    private function getUsageCounts(): array
    {
        $typeId = $this->models['main']->getId();
        return [
            'contents' => Content::query()->andWhere([
                'typeId' => $typeId,
            ])->count(),
            'tags' => Tag::query()->andWhere([
                'typeId' => $typeId,
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

        if ($usage['contents'] > 0 || $usage['tags'] > 0) {
            $message = $this->translator->translate('Type "{name}" cannot be deleted because it is used by {contents, plural, =0{no content} one{# content} other{# contents}} and {tags, plural, =0{no tag} one{# tag} other{# tags}}.', [
                'name' => $model->getName(),
                'contents' => $usage['contents'],
                'tags' => $usage['tags'],
            ], 'dboard-modules');

            $content = (string) $this->renderPartial('Types/_delete-error-content', [
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
