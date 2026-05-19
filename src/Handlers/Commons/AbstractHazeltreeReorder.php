<?php

declare(strict_types=1);

/**
 * AbstractHazeltreeReorder.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Enums\TreePosition;
use Blackcube\Dboard\Models\Forms\HazeltreeReorderForm;
use Blackcube\Bleet\Enums\AjaxifyAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Router\CurrentRoute;

/**
 * Abstract action for Hazeltree tree drag-drop reordering.
 * Inherits from AbstractAjaxHandler and uses ActionModel configuration.
 *
 * Pipeline: setupAction() -> setupMethod() -> try { handleMethod() } catch -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractHazeltreeReorder extends AbstractAjaxHandler
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
     * @return string The entity name (e.g., 'content', 'tag', 'menu')
     */
    abstract protected function getEntityName(): string;

    /**
     * Returns the DOM element ID for the list container.
     *
     * @return string The DOM element ID
     */
    abstract protected function getListId(): string;

    /**
     * Returns the route name for refreshing the list.
     *
     * @return string The route name
     */
    abstract protected function getListRoute(): string;

    /**
     * Returns the maximum allowed tree depth.
     *
     * @return int The maximum level
     */
    abstract protected function getMaxLevel(): int;

    /**
     * @var HazeltreeReorderForm|null The reorder form model
     */
    protected ?HazeltreeReorderForm $reorderForm = null;

    /**
     * @var TreePosition|null The tree position
     */
    protected ?TreePosition $treePosition = null;

    /**
     * @var ActiveRecord|null The source element
     */
    protected ?ActiveRecord $source = null;

    /**
     * @var ActiveRecord|null The target element
     */
    protected ?ActiveRecord $target = null;

    /**
     * @var array The descendants of the source element
     */
    protected array $descendants = [];

    /**
     * @var int The depth of the subtree being moved
     */
    protected int $subtreeDepth = 0;

    /**
     * @var bool Whether the move was successful
     */
    protected bool $moved = false;

    /**
     * {@inheritdoc}
     *
     * Note: We don't use ActionModel for source/target because they come from body params,
     * not from route arguments. We load them manually in setupAction.
     */
    protected function getActionModels(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * No primary keys from route - source/target come from body params.
     */
    protected function extractPrimaryKeysFromRoute(): array
    {
        return [];
    }

    /**
     * Sets up the action: validates form and loads source/target models.
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

        // Validate form (DnD uses empty scope)
        $this->reorderForm = new HazeltreeReorderForm(translator: $this->translator);
        $this->reorderForm->load($bodyParams, '');

        if (!$this->reorderForm->validate()) {
            throw new \RuntimeException('Invalid parameters.');
        }

        // Parse position
        $this->treePosition = TreePosition::from($this->reorderForm->getPosition());

        // Load source
        $modelClass = $this->getModelClass();
        $entityName = $this->getEntityName();

        $this->source = $modelClass::query()
            ->andWhere(['id' => (int) $this->reorderForm->getSourceId()])
            ->one();

        if ($this->source === null) {
            throw new \RuntimeException('Source ' . $entityName . ' not found.');
        }

        // Load target
        $this->target = $modelClass::query()
            ->andWhere(['id' => (int) $this->reorderForm->getTargetId()])
            ->one();

        if ($this->target === null) {
            throw new \RuntimeException('Target ' . $entityName . ' not found.');
        }

        // Load descendants
        $this->descendants = [];
        $descendantsQuery = $this->source->relativeQuery()
            ->children()
            ->includeDescendants();
        foreach ($descendantsQuery->each() as $descendant) {
            $this->descendants[] = $descendant;
        }

        // Validate move
        $this->validateMove();

        return null;
    }

    /**
     * Validates the move operation.
     *
     * @throws \RuntimeException If the move is not allowed
     */
    protected function validateMove(): void
    {
        // Check that target is not a descendant of source
        foreach ($this->descendants as $descendant) {
            if ($descendant->getId() === $this->target->getId()) {
                throw new \RuntimeException('Cannot move an element into one of its children.');
            }
        }

        // Check max level
        $sourceLevel = $this->source->getLevel();
        $maxDescendantLevel = $sourceLevel;
        foreach ($this->descendants as $descendant) {
            if ($descendant->getLevel() > $maxDescendantLevel) {
                $maxDescendantLevel = $descendant->getLevel();
            }
        }
        $this->subtreeDepth = $maxDescendantLevel - $sourceLevel;

        $newSourceLevel = match ($this->treePosition) {
            TreePosition::Into => $this->target->getLevel() + 1,
            TreePosition::Before, TreePosition::After => $this->target->getLevel(),
        };

        $wouldBeMaxLevel = $newSourceLevel + $this->subtreeDepth;

        if ($wouldBeMaxLevel > $this->getMaxLevel()) {
            throw new \RuntimeException(
                'Maximum depth of ' . $this->getMaxLevel() . ' levels exceeded.'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setupMethod(): void
    {
        // Nothing to do, form already loaded in setupAction
    }

    /**
     * Hook called before reorder.
     * Note: Hazeltree manages its own transactions internally.
     *
     * @param bool $inTransaction Whether we are inside the transaction (always false here)
     */
    protected function beforeReorder(bool $inTransaction): void
    {
        // Hook for subclasses
    }

    /**
     * Hook called after reorder.
     * Note: Hazeltree manages its own transactions internally.
     *
     * @param bool $inTransaction Whether we are inside the transaction (always false here)
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
        $this->beforeReorder(false);

        $success = match ($this->treePosition) {
            TreePosition::Before => $this->source->saveBefore($this->target),
            TreePosition::Into => $this->source->saveInto($this->target),
            TreePosition::After => $this->source->saveAfter($this->target),
        };

        if (!$success) {
            throw new \RuntimeException('Move failed.');
        }

        $this->afterReorder(false);

        $this->moved = true;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $entityName = $this->getEntityName();

        return [
            'type' => OutputType::Json->value,
            'data' => [
                ...AureliaCommunication::ajaxify(
                    $this->getListId(),
                    $this->urlGenerator->generate($this->getListRoute()),
                    AjaxifyAction::Refresh
                ),
                ...AureliaCommunication::toast(
                    $this->translator->translate('Success', category: 'dboard-common'),
                    $this->translator->translate('{entity} moved.', ['entity' => ucfirst($entityName)], 'dboard-common'),
                    UiColor::Success
                ),
            ],
        ];
    }
}