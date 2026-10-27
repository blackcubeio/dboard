<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\ElasticSchemas;

use Blackcube\Dcore\Models\Bloc;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Handlers\Commons\AbstractPanel;
use Blackcube\Handler\OutputType;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Yiisoft\Router\CurrentRoute;

/**
 * ElasticSchema delete init action (GET).
 * Displays delete confirmation modal, or error if schema is used.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class DeleteInit extends AbstractPanel
{
    protected function getType(): DialogType { return DialogType::Modal; }
    protected function getModelClass(): string { return ElasticSchema::class; }
    protected function getTitle(): string { return $this->translator->translate('Delete schema', category: 'dboard-modules'); }
    protected function getContentView(): string { return 'Commons/_delete-content'; }
    protected function getMessage(): string { return $this->translator->translate('Schema "{name}" will be permanently deleted.', [
        'name' => $this->getModelName(),
    ], 'dboard-modules'); }
    protected function getColor(): UiColor { return UiColor::Danger; }

    /**
     * Check if schema is used by Bloc, Content or Tag.
     *
     * @return array{blocs: int, contents: int, tags: int} Usage counts
     */
    private function getUsageCounts(): array
    {
        $schemaId = $this->models['main']->getId();
        return [
            'blocs' => Bloc::query()->andWhere([
                'elasticSchemaId' => $schemaId,
            ])->count(),
            'contents' => Content::query()->andWhere([
                'elasticSchemaId' => $schemaId,
            ])->count(),
            'tags' => Tag::query()->andWhere([
                'elasticSchemaId' => $schemaId,
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

        if ($usage['blocs'] > 0 || $usage['contents'] > 0 || $usage['tags'] > 0) {
            $message = $this->translator->translate('Schema "{name}" cannot be deleted because it is used by {blocs, plural, =0{no block} one{# block} other{# blocks}}, {contents, plural, =0{no content} one{# content} other{# contents}} and {tags, plural, =0{no tag} one{# tag} other{# tags}}.', [
                'name' => $model->getName(),
                'blocs' => $usage['blocs'],
                'contents' => $usage['contents'],
                'tags' => $usage['tags'],
            ], 'dboard-modules');

            $content = (string) $this->renderPartial('ElasticSchemas/_delete-error-content', [
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
