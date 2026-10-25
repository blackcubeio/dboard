<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Enums\UiLevel;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * Abstract toggle action for activating/deactivating models.
 * Inherits from AbstractAjaxHandler and uses ActionModel configuration.
 * Uses common views from Views/Commons/ for the confirmation modal.
 *
 * Pipeline: setupAction() -> setupMethod() -> try { handleMethod() } catch -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractToggle extends AbstractAjaxHandler
{
    /**
     * Returns the model class name.
     *
     * @return string Fully qualified class name of the ActiveRecord model
     */
    abstract protected function getModelClass(): string;

    /**
     * Returns the entity name for display messages.
     *
     * @return string The entity name (e.g., 'contenu', 'tag')
     */
    abstract protected function getEntityName(): string;

    /**
     * Returns the list container ID for ajaxify refresh.
     *
     * @return string The DOM element ID
     */
    abstract protected function getListId(): string;

    /**
     * Returns the route name for the list refresh.
     *
     * @return string The route name
     */
    abstract protected function getListRoute(): string;

    /**
     * Returns the model name for display messages.
     * Override this method if the model uses a different method than getName().
     *
     * @return string The model name
     */
    protected function getModelName(): string
    {
        return $this->models['main']->getName();
    }

    /**
     * {@inheritdoc}
     */
    protected function getActionModels(): array
    {
        return [
            'main' => new ActionModel(
                modelClass: $this->getModelClass(),
                formModelClass: null,
                isMain: true
            ),
        ];
    }

    /**
     * Hook called before toggle.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function beforeToggle(bool $inTransaction): void
    {
    }

    /**
     * Hook called after toggle.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function afterToggle(bool $inTransaction): void
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function handleMethod(): void
    {
        if ($this->request->getMethod() === Method::POST) {
            $model = $this->models['main'];
            $model->setActive($model->isActive() === false);

            $this->beforeToggle(false);
            $transaction = $model->db()->beginTransaction();
            try {
                $this->beforeToggle(true);
                $model->save();
                $this->afterToggle(true);
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
            $this->afterToggle(false);
        }
    }

    /**
     * Returns the toast message shown once the entity is toggled.
     * Override this method to give the entity its own translated message.
     *
     * @return string The success message
     */
    protected function getSuccessMessage(): string
    {
        return $this->translator->translate('Element "{name}" has been {status, select, enabled{enabled} other{disabled}}.', [
            'name' => $this->getModelName(),
            'status' => $this->models['main']->isActive() === true ? 'enabled' : 'disabled',
        ], 'dboard-common');
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $model = $this->models['main'];
        $entityName = $this->getEntityName();

        if ($this->request->getMethod() === Method::POST) {
            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Close),
                    ...AureliaCommunication::ajaxify(
                        $this->getListId(),
                        $this->urlGenerator->generate($this->getListRoute())
                    ),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-common'),
                        $this->getSuccessMessage(),
                        UiLevel::Success
                    ),
                ],
            ];
        } else {
            $content = (string) $this->renderPartial('Commons/_toggle-content', [
                'model' => $model,
                'modelName' => $this->getModelName(),
                'entityLabel' => $entityName,
                'urlGenerator' => $this->urlGenerator,
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