<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dcore\Models\Bloc;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Handler\OutputType;
use Blackcube\Dboard\Enums\ReorderMode;
use Blackcube\Dboard\Models\Forms\BlocForm;
use Blackcube\Bleet\Enums\AjaxifyAction;
use Blackcube\Bleet\Enums\UiLevel;
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
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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
                formModelClass: null,
                isMain: true,
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
        $setupResponse = parent::setupAction();
        if ($setupResponse === null) {
            $bodyParams = $this->getBodyParams() ?? [];

            $this->blocForm = new BlocForm(translator: $this->translator);

            $this->blocForm->setScenario('move');
            $this->blocForm->load($bodyParams);
            if ($this->blocForm->validate() === true) {
                $this->reorderMode = ReorderMode::from($this->blocForm->getMode());
            } else {
                $this->blocForm->setScenario('dnd');
                $this->blocForm->load($bodyParams, '');
                if ($this->blocForm->validate() === true) {
                    $this->reorderMode = ReorderMode::Dnd;
                } else {
                    throw new \RuntimeException('Invalid reorder mode.');
                }
            }
        }

        return $setupResponse;
    }

    /**
     * Hook called before reorder.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function beforeReorder(bool $inTransaction): void
    {
    }

    /**
     * Hook called after reorder.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function afterReorder(bool $inTransaction): void
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function handleMethod(): void
    {
        if ($this->request->getMethod() === Method::POST) {
            match ($this->reorderMode) {
                ReorderMode::MoveUp, ReorderMode::MoveDown => $this->handleMoveReorder(),
                ReorderMode::Dnd => $this->handleDndReorder(),
            };
        }
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
            ->andWhere([
                'id' => (int) $blocId,
            ])
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
            $orderedBlocIds = [];
            foreach ($order as $blocId) {
                $orderedBlocIds[] = (int) $blocId;
            }
            $orderedBlocs = [];
            if (empty($orderedBlocIds) === false) {
                $orderedBlocsQuery = Bloc::query()->andWhere([
                    'in',
                    'id',
                    $orderedBlocIds,
                ]);
                /** @var Bloc $orderedBloc */
                foreach ($orderedBlocsQuery->each() as $orderedBloc) {
                    $orderedBlocs[$orderedBloc->getId()] = $orderedBloc;
                }
            }
            $position = 1;
            foreach ($orderedBlocIds as $orderedBlocId) {
                if (isset($orderedBlocs[$orderedBlocId]) === true) {
                    $model->moveBloc($orderedBlocs[$orderedBlocId], $position);
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
        $refreshParams = $this->extractPrimaryKeysFromModel();
        if ($this->reorderMode === ReorderMode::Dnd) {
            $refreshParams['dndMode'] = '1';
        }

        $aureliaCommunication = [
            ...AureliaCommunication::ajaxify(
                $this->getBlocsListId(),
                $this->urlGenerator->generate($this->getEditRoute(), $refreshParams),
                AjaxifyAction::Run,
            ),
        ];

        if ($this->reorderMode === ReorderMode::Dnd) {
            $aureliaCommunication = [
                ...$aureliaCommunication,
                ...AureliaCommunication::toast(
                    $this->translator->translate('Success', category: 'dboard-common'),
                    $this->translator->translate('Block order updated.', category: 'dboard-common'),
                    UiLevel::Success
                ),
            ];
        }

        return [
            'type' => OutputType::Json->value,
            'data' => $aureliaCommunication,
        ];
    }
}