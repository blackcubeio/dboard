<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

use Blackcube\Dcore\Models\GlobalXeo;
use Blackcube\Dboard\Handlers\Commons\AbstractDelete;
use Blackcube\Handler\OutputType;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * Abstract delete action for GlobalXeo kinds.
 * Handles composite PK (hostId, kind).
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractGlobalXeoDelete extends AbstractDelete
{
    abstract protected function getKind(): string;
    abstract protected function getKindLabel(): string;
    abstract protected function getIndexRoute(): string;

    protected function getModelClass(): string { return GlobalXeo::class; }
    protected function getEntityName(): string { return $this->getKindLabel(); }
    protected function getEntityLabel(): string { return $this->getKindLabel(); }
    protected function getListId(): string { return 'xeo-' . strtolower($this->getKind()) . '-list'; }
    protected function getListRoute(): string { return $this->getIndexRoute(); }

    protected function getSuccessMessage(): string
    {
        return $this->translator->translate('{kind, select, Organization{Organization "{name}" has been deleted.} WebSite{Website "{name}" has been deleted.} Sitemap{Additional sitemap "{name}" has been deleted.} other{robots.txt "{name}" has been deleted.}}', [
            'kind' => $this->getKind(),
            'name' => $this->getModelName(),
        ], 'dboard-modules');
    }

    protected function primaryKeys(): array
    {
        return [
            'hostId',
            'kind',
        ];
    }

    protected function extractPrimaryKeysFromRoute(): array
    {
        return [
            'main' => [
                'hostId' => $this->currentRoute->getArgument('hostId'),
                'kind' => $this->getKind(),
            ],
        ];
    }

    protected function extractPrimaryKeysFromModel(string $name = 'main'): array
    {
        return [
            'hostId' => $this->models[$name]->getHostId(),
        ];
    }

    protected function prepareOutputData(): array
    {
        if ($this->request->getMethod() === Method::DELETE) {
            $outputData = parent::prepareOutputData();
        } else {
            $message = $this->translator->translate('{kind, select, Organization{Organization "{name}" will be permanently deleted.} WebSite{Website "{name}" will be permanently deleted.} Sitemap{Additional sitemap "{name}" will be permanently deleted.} other{robots.txt "{name}" will be permanently deleted.}}', [
                'kind' => $this->getKind(),
                'name' => $this->getModelName(),
            ], 'dboard-modules');

            $content = (string) $this->renderPartial('Commons/_delete-content', [
                'message' => $message,
                'formAction' => $this->urlGenerator->generate(
                    $this->currentRoute->getName(),
                    $this->extractPrimaryKeysFromModel()
                ),
            ])->getBody();

            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Keep),
                    ...AureliaCommunication::dialogContent(Bleet::dialogFrame(DialogType::Modal)
                        ->title($this->translator->translate('Deletion', category: 'dboard-common'))
                        ->danger()
                        ->content($content)
                        ->render()),
                ],
            ];
        }

        return $outputData;
    }
}
