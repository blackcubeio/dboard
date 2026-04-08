<?php

declare(strict_types=1);

/**
 * AbstractPanel.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Enums\PanelType;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Router\CurrentRoute;

/**
 * Abstract panel action for modal and drawer display (GET only).
 * The actual action (POST/DELETE) is handled by a separate class.
 *
 * Pipeline: setupAction() -> setupMethod() -> handleMethod() -> prepareOutputData() -> renderJson()
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractPanel extends AbstractAjaxHandler
{
    // === CONFIGURATION (concrètes) ===

    abstract protected function getType(): PanelType;
    abstract protected function getModelClass(): string;
    abstract protected function getTitle(): string;
    abstract protected function getContentView(): string;
    abstract protected function getMessage(): string;

    protected function getColor(): UiColor { return UiColor::Primary; }
    protected function getModelName(): string { return $this->models['main']->getName(); }

    // === PIPELINE : ActionModels ===

    /**
     * {@inheritdoc}
     */
    protected function getActionModels(): array
    {
        return [
            'main' => new ActionModel(
                modelClass: $this->getModelClass(),
                formModelClass: null,
                isMain: true,
            ),
        ];
    }

    // === PIPELINE : setupAction ===

    /**
     * {@inheritdoc}
    protected function setupAction(): ?ResponseInterface
    {
        return parent::setupAction();
    }
    */

    // === PIPELINE : setupMethod ===

    /**
     * {@inheritdoc}
    protected function setupMethod(): void
    {
        // GET only - nothing to setup
    }
     */

    // === PIPELINE : handleMethod ===

    /**
     * {@inheritdoc}
    protected function handleMethod(): void
    {
        // GET only - no action to handle
    }
     */

    // === PIPELINE : prepareOutputData ===

    protected function getHeaderView(): string
    {
        return match ($this->getType()) {
            PanelType::Modal => 'Commons/_modal-header',
            PanelType::Drawer => 'Commons/_drawer-header',
        };
    }

    protected function getViewData(): array
    {
        return [
            'type' => $this->getType(),
            'model' => $this->models['main'],
            'modelName' => $this->getModelName(),
            'message' => $this->getMessage(),
            'urlGenerator' => $this->urlGenerator,
            'formAction' => $this->urlGenerator->generate(
                $this->currentRoute->getName(),
                $this->extractPrimaryKeysFromModel()
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $header = (string) $this->renderPartial($this->getHeaderView(), [
            'title' => $this->getTitle(),
            'uiColor' => $this->getColor(),
        ])->getBody();

        $content = (string) $this->renderPartial(
            $this->getContentView(),
            $this->getViewData()
        )->getBody();

        return [
            'type' => OutputType::Json->value,
            'data' => [
                ...AureliaCommunication::dialog(DialogAction::Keep),
                ...AureliaCommunication::dialogContent($header, $content, $this->getColor()),
            ],
        ];
    }
}
