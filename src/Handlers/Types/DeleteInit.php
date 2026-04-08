<?php

declare(strict_types=1);

/**
 * DeleteInit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Types;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dcore\Models\Type;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Enums\PanelType;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Yiisoft\Router\CurrentRoute;

/**
 * Type delete init action (GET).
 * Displays delete confirmation modal, or error if type is used.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class DeleteInit extends AbstractPanel
{
    protected function getType(): PanelType { return PanelType::Modal; }
    protected function getModelClass(): string { return Type::class; }
    protected function getTitle(): string { return $this->translator->translate('Delete type', category: 'dboard-modules'); }
    protected function getContentView(): string { return 'Commons/_delete-content'; }
    protected function getMessage(): string { return $this->translator->translate('Type "{name}" will be permanently deleted.', ['name' => $this->getModelName()], 'dboard-modules'); }
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
            'contents' => Content::query()->andWhere(['typeId' => $typeId])->count(),
            'tags' => Tag::query()->andWhere(['typeId' => $typeId])->count(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $model = $this->models['main'];
        $usage = $this->getUsageCounts();

        // Type utilisé → afficher erreur
        if ($usage['contents'] > 0 || $usage['tags'] > 0) {
            $header = (string) $this->renderPartial('Commons/_modal-header', [
                'title' => $this->translator->translate('Deletion not possible', category: 'dboard-modules'),
                'uiColor' => UiColor::Danger,
            ])->getBody();

            $parts = [];
            if ($usage['contents'] > 0) {
                $parts[] = $usage['contents'] . ' content(s)';
            }
            if ($usage['tags'] > 0) {
                $parts[] = $usage['tags'] . ' tag(s)';
            }
            $message = $this->translator->translate('Type "{name}" cannot be deleted because it is used by {usage}.', ['name' => $model->getName(), 'usage' => implode(' and ', $parts)], 'dboard-modules');

            $content = (string) $this->renderPartial('Types/_delete-error-content', [
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

        // Type non utilisé → confirmation normale (parent)
        return parent::prepareOutputData();
    }
}
