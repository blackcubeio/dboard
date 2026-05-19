<?php

declare(strict_types=1);

/**
 * AbstractGlobalXeoDelete.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

use Blackcube\Dcore\Models\GlobalXeo;
use Blackcube\Dboard\Handlers\Commons\AbstractDelete;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * Abstract delete action for GlobalXeo kinds.
 * Handles composite PK (hostId, kind).
 *
 * @copyright 2010-2026 Blackcube
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

    protected function primaryKeys(): array
    {
        return ['hostId', 'kind'];
    }

    protected function extractPrimaryKeysFromRoute(): array
    {
        return ['main' => [
            'hostId' => $this->currentRoute->getArgument('hostId'),
            'kind' => $this->getKind(),
        ]];
    }

    protected function extractPrimaryKeysFromModel(string $name = 'main'): array
    {
        return ['hostId' => $this->models[$name]->getHostId()];
    }

    protected function prepareOutputData(): array
    {
        // DELETE: delegate to parent (toast + close + ajaxify)
        if ($this->request->getMethod() === Method::DELETE) {
            return parent::prepareOutputData();
        }

        // GET: modal confirmation with $message (like AbstractPanel pattern)
        $message = $this->translator->translate('{entity} "{name}" will be permanently deleted.', ['entity' => ucfirst($this->getKindLabel()), 'name' => $this->getModelName()], 'dboard-common');

        $header = (string) $this->renderPartial('Commons/_modal-header', [
            'title' => $this->translator->translate('Deletion', category: 'dboard-common'),
            'uiColor' => UiColor::Danger,
        ])->getBody();

        $content = (string) $this->renderPartial('Commons/_delete-content', [
            'message' => $message,
            'formAction' => $this->urlGenerator->generate(
                $this->currentRoute->getName(),
                $this->extractPrimaryKeysFromModel()
            ),
        ])->getBody();

        return [
            'type' => OutputType::Json->value,
            'data' => [
                ...AureliaCommunication::dialog(DialogAction::Keep),
                ...AureliaCommunication::dialogContent($header, $content, UiColor::Danger),
            ],
        ];
    }
}
