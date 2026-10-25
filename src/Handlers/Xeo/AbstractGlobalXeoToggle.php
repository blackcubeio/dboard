<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

use Blackcube\Dcore\Models\GlobalXeo;
use Blackcube\Dboard\Handlers\Commons\AbstractToggle;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * Abstract toggle action for GlobalXeo kinds.
 * Handles composite PK (hostId, kind).
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractGlobalXeoToggle extends AbstractToggle
{
    abstract protected function getKind(): string;
    abstract protected function getKindLabel(): string;
    abstract protected function getIndexRoute(): string;

    protected function getModelClass(): string { return GlobalXeo::class; }
    protected function getEntityName(): string { return $this->getKindLabel(); }
    protected function getListId(): string { return 'xeo-' . strtolower($this->getKind()) . '-list'; }
    protected function getListRoute(): string { return $this->getIndexRoute(); }

    protected function getSuccessMessage(): string
    {
        return $this->translator->translate('{kind, select, Organization{Organization "{name}" has been {status, select, enabled{enabled} other{disabled}}.} WebSite{Website "{name}" has been {status, select, enabled{enabled} other{disabled}}.} Sitemap{Additional sitemap "{name}" has been {status, select, enabled{enabled} other{disabled}}.} other{robots.txt "{name}" has been {status, select, enabled{enabled} other{disabled}}.}}', [
            'kind' => $this->getKind(),
            'name' => $this->getModelName(),
            'status' => $this->models['main']->isActive() === true ? 'enabled' : 'disabled',
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
        $model = $this->models['main'];

        if ($this->request->getMethod() === Method::POST) {
            $outputData = parent::prepareOutputData();
        } else {
            $message = $this->translator->translate('{kind, select, Organization{Organization "{name}" will be {status, select, enabled{enabled} other{disabled}}.} WebSite{Website "{name}" will be {status, select, enabled{enabled} other{disabled}}.} Sitemap{Additional sitemap "{name}" will be {status, select, enabled{enabled} other{disabled}}.} other{robots.txt "{name}" will be {status, select, enabled{enabled} other{disabled}}.}}', [
                'kind' => $this->getKind(),
                'name' => $this->getModelName(),
                'status' => $model->isActive() === true ? 'disabled' : 'enabled',
            ], 'dboard-modules');

            $content = (string) $this->renderPartial('Commons/_toggle-content', [
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
                        ->title($this->translator->translate('Modification', category: 'dboard-common'))
                        ->warning()
                        ->content($content)
                        ->render()),
                ],
            ];
        }

        return $outputData;
    }
}
