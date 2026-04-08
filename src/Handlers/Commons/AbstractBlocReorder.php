<?php

declare(strict_types=1);

/**
 * AbstractBlocReorder.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dcore\Models\Bloc;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Enums\ReorderMode;
use Blackcube\Dboard\Models\Forms\BlocForm;
use Blackcube\Bleet\Enums\AjaxifyAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * Abstract action for reordering blocs of an entity.
 * Inherits from AbstractAjaxHandler and uses ActionModel configuration.
 * Uses BlocForm with scenario 'move' or 'dnd' and ReorderMode enum.
 *
 * Pipeline: setupAction() -> setupMethod() -> try { handleMethod() } catch -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractBlocReorder extends AbstractAjaxHandler
{
    /**
     * Returns the model class name (parent entity).
     *
     * @return string Fully qualified class name of the ActiveRecord model
     */
    abstract protected function getModelClass(): string;

    /**
     * Returns the entity name for display messages.
     *
     * @return string The entity name (e.g., 'content', 'tag')
     */
    abstract protected function getEntityName(): string;

    /**
     * Returns the route name for the edit page (refresh blocs list).
     *
     * @return string The route name
     */
    abstract protected function getEditRoute(): string;

    /**
     * Returns the DOM element ID for the blocs list container.
     *
     * @return string The DOM element ID
     */
    abstract protected function getBlocsListId(): string;

    /**
     * @var BlocForm|null The bloc form
     */
    protected ?BlocForm $blocForm = null;

    /**
     * @var ReorderMode|null The reorder mode
     */
    protected ?ReorderMode $reorderMode = null;

    /**
     * {@inheritdoc}
     */
    protected function getActionModels(): array
    {
        return [
            'main' => new ActionModel(
                modelClass: $this->getModelClass(),
                formModelClass: null, // No formModel for the parent
                isMain: true, // 404 if parent not found
            ),
        ];
    }

    /**
     * Sets up the action and prepares BlocForm with appropriate scenario.
     *
     * @return ResponseInterface|null Response if setup failed, null if successful
     */
    protected function setupAction(): ?ResponseInterface
    {
        $response = parent::setupAction();
        if ($response !== null) {
            return $response;
        }

        $bodyParams = $this->getBodyParams() ?? [];

        $this->blocForm = new BlocForm(translator: $this->translator);

        // Try move scenario first (normal scope)
        $this->blocForm->setScenario('move');
        $this->blocForm->load($bodyParams);
        if ($this->blocForm->validate()) {
            $this->reorderMode = ReorderMode::from($this->blocForm->getMode());
            return null;
        }

        // Try dnd scenario (empty scope)
        $this->blocForm->setScenario('dnd');
        $this->blocForm->load($bodyParams, '');
        if ($this->blocForm->validate()) {
            $this->reorderMode = ReorderMode::Dnd;
            return null;
        }

        throw new \RuntimeException('Invalid reorder mode.');
    }

    /**
     * Hook called before reorder.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function beforeReorder(bool $inTransaction): void
    {
        // Hook for subclasses
    }

    /**
     * Hook called after reorder.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function afterReorder(bool $inTransaction): void
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

        // Validate already done in setupAction

        match ($this->reorderMode) {
            ReorderMode::MoveUp, ReorderMode::MoveDown => $this->handleMoveReorder(),
            ReorderMode::Dnd => $this->handleDndReorder(),
        };
    }

    /**
     * Handles move up/down reorder.
     *
     * @return void
     * @throws \RuntimeException If bloc not found
     */
    protected function handleMoveReorder(): void
    {
        $blocId = $this->blocForm->getBlocId();
        $bloc = Bloc::query()
            ->andWhere(['id' => (int) $blocId])
            ->one();

        if ($bloc === null) {
            throw new \RuntimeException('Block not found.');
        }

        $model = $this->models['main'];
        $mode = $this->blocForm->getMode();

        $this->beforeReorder(false);
        $transaction = $model->db()->beginTransaction();
        try {
            $this->beforeReorder(true);
            if ($mode === ReorderMode::MoveUp->value) {
                $model->moveBlocUp($bloc);
            } elseif ($mode === ReorderMode::MoveDown->value) {
                $model->moveBlocDown($bloc);
            }
            $this->afterReorder(true);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        $this->afterReorder(false);
    }

    /**
     * Handles drag-n-drop reorder.
     *
     * @return void
     */
    protected function handleDndReorder(): void
    {
        $order = $this->blocForm->getOrder();
        $model = $this->models['main'];

        $this->beforeReorder(false);
        $transaction = $model->db()->beginTransaction();
        try {
            $this->beforeReorder(true);
            $position = 1;
            foreach ($order as $blocId) {
                $bloc = Bloc::query()
                    ->andWhere(['id' => (int) $blocId])
                    ->one();
                if ($bloc !== null) {
                    $model->moveBloc($bloc, $position);
                    $position++;
                }
            }
            $this->afterReorder(true);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        $this->afterReorder(false);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        // For DnD, add dndMode param to refresh URL
        $refreshParams = $this->extractPrimaryKeysFromModel();
        if ($this->reorderMode === ReorderMode::Dnd) {
            $refreshParams['dndMode'] = '1';
        }

        $data = [
            ...AureliaCommunication::ajaxify(
                $this->getBlocsListId(),
                $this->urlGenerator->generate($this->getEditRoute(), $refreshParams),
                AjaxifyAction::Run,
            ),
        ];

        // Add toast only for DnD
        if ($this->reorderMode === ReorderMode::Dnd) {
            $data = [
                ...$data,
                ...AureliaCommunication::toast(
                    $this->translator->translate('Success', category: 'dboard-common'),
                    $this->translator->translate('Block order updated.', category: 'dboard-common'),
                    UiColor::Success
                ),
            ];
        }

        return [
            'type' => OutputType::Json->value,
            'data' => $data,
        ];
    }
}