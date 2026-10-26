<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Handler\OutputType;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
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
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractPanel extends AbstractAjaxHandler
{

    abstract protected function getType(): DialogType;
    abstract protected function getModelClass(): string;
    abstract protected function getTitle(): string;
    abstract protected function getContentView(): string;
    abstract protected function getMessage(): string;

    protected function getColor(): UiColor { return UiColor::Primary; }
    protected function getModelName(): string { return $this->models['main']->getName(); }


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


    /**
     * {@inheritdoc}
    protected function setupAction(): ?ResponseInterface
    {
        return parent::setupAction();
    }
    */


    /**
     * {@inheritdoc}
    protected function setupMethod(): void
    {
    }
     */


    /**
     * {@inheritdoc}
    protected function handleMethod(): void
    {
    }
     */


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
        $content = (string) $this->renderPartial(
            $this->getContentView(),
            $this->getViewData()
        )->getBody();

        return [
            'type' => OutputType::Json->value,
            'data' => [
                ...AureliaCommunication::dialog(DialogAction::Keep),
                ...AureliaCommunication::dialogContent(Bleet::dialogFrame($this->getType())
                    ->title($this->getTitle())
                    ->color($this->getColor()->value)
                    ->content($content)
                    ->render()),
            ],
        ];
    }
}
