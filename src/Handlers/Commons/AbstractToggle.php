<?php

declare(strict_types=1);

/**
 * AbstractToggle.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
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
 * @copyright 2010-2026 Blackcube
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
        // Hook for subclasses
    }

    /**
     * Hook called after toggle.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function afterToggle(bool $inTransaction): void
    {
        // Hook for subclasses
    }

    /**
     * {@inheritdoc}
     */
    protected function handleMethod(): void
    {
        if ($this->request->getMethod() !== Method::POST) {
            return;
        }

        $model = $this->models['main'];
        $model->setActive(!$model->isActive());

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

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $model = $this->models['main'];
        $entityName = $this->getEntityName();

        // POST: toast + close + ajaxify
        if ($this->request->getMethod() === Method::POST) {
            $status = $model->isActive()
                ? $this->translator->translate('enabled', category: 'dboard-common')
                : $this->translator->translate('disabled', category: 'dboard-common');
            return [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Close),
                    ...AureliaCommunication::ajaxify(
                        $this->getListId(),
                        $this->urlGenerator->generate($this->getListRoute())
                    ),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-common'),
                        $this->translator->translate('{entity} "{name}" {status}.', ['entity' => ucfirst($entityName), 'name' => $this->getModelName(), 'status' => $status], 'dboard-common'),
                        UiColor::Success
                    ),
                ],
            ];
        }

        // GET: modal confirmation (common views)
        $header = (string) $this->renderPartial('Commons/_modal-header', [
            'title' => $this->translator->translate('Modification', category: 'dboard-common'),
            'uiColor' => UiColor::Warning,
        ])->getBody();

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

        return [
            'type' => OutputType::Json->value,
            'data' => [
                ...AureliaCommunication::dialog(DialogAction::Keep),
                ...AureliaCommunication::dialogContent($header, $content, UiColor::Warning),
            ],
        ];
    }
}