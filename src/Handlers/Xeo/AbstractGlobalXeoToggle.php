<?php

declare(strict_types=1);

/**
 * AbstractGlobalXeoToggle.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Xeo;

use Blackcube\Dcore\Models\GlobalXeo;
use Blackcube\Dboard\Handlers\Commons\AbstractToggle;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * Abstract toggle action for GlobalXeo kinds.
 * Handles composite PK (hostId, kind).
 *
 * @copyright 2010-2026 Blackcube
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
        $model = $this->models['main'];

        // POST: delegate to parent (toast + close + ajaxify)
        if ($this->request->getMethod() === Method::POST) {
            return parent::prepareOutputData();
        }

        // GET: modal confirmation with $message (like AbstractPanel pattern)
        $status = $model->isActive()
            ? $this->translator->translate('disabled', category: 'dboard-common')
            : $this->translator->translate('enabled', category: 'dboard-common');
        $message = $this->translator->translate('{entity} "{name}" will be {status}.', ['entity' => ucfirst($this->getKindLabel()), 'name' => $this->getModelName(), 'status' => $status], 'dboard-common');

        $header = (string) $this->renderPartial('Commons/_modal-header', [
            'title' => $this->translator->translate('Modification', category: 'dboard-common'),
            'uiColor' => UiColor::Warning,
        ])->getBody();

        $content = (string) $this->renderPartial('Commons/_toggle-content', [
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
                ...AureliaCommunication::dialogContent($header, $content, UiColor::Warning),
            ],
        ];
    }
}
