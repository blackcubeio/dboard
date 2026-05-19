<?php

declare(strict_types=1);

/**
 * AbstractBlocAdd.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dcore\Models\Bloc;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Forms\BlocForm;
use Blackcube\Bleet\Enums\AjaxifyAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Blackcube\ActiveRecord\Elastic\ElasticSchema;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * Abstract action for adding a bloc to an entity.
 * Inherits from AbstractAjaxHandler and uses ActionModel configuration.
 * Uses BlocForm with scenario 'add' and normal scope.
 *
 * Pipeline: setupAction() -> setupMethod() -> try { handleMethod() } catch -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractBlocAdd extends AbstractAjaxHandler
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
     * Returns the pivot class name (e.g., ContentBloc).
     *
     * @return string Fully qualified class name of the pivot model
     */
    abstract protected function getPivotClass(): string;

    /**
     * Returns the pivot foreign key column name (e.g., 'contentId').
     *
     * @return string The column name
     */
    abstract protected function getPivotFkColumn(): string;

    /**
     * @var Bloc|null The newly created bloc
     */
    protected ?Bloc $bloc = null;

    /**
     * @var ElasticSchema|null The elastic schema for the bloc
     */
    protected ?ElasticSchema $elasticSchema = null;

    /**
     * @var ActiveRecord|null The entity type
     */
    protected ?ActiveRecord $type = null;

    /**
     * @var BlocForm|null The bloc form
     */
    protected ?BlocForm $blocForm = null;

    /**
     * @var int The insert position for the bloc
     */
    protected int $insertPosition = 0;

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
     * Sets up the action and prepares BlocForm.
     *
     * @return ResponseInterface|null Response if setup failed, null if successful
     */
    protected function setupAction(): ?ResponseInterface
    {
        $response = parent::setupAction();
        if ($response !== null) {
            return $response;
        }

        // Load BlocForm from bodyParams (normal scope)
        $this->blocForm = new BlocForm(translator: $this->translator);
        $this->blocForm->setScenario('add');
        $this->blocForm->load($this->getBodyParams() ?? []);

        return null;
    }

    /**
     * Hook called before save.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function beforeSave(bool $inTransaction): void
    {
        // Hook for subclasses
    }

    /**
     * Hook called after save.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function afterSave(bool $inTransaction): void
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

        // Validate form
        if (!$this->blocForm->validate()) {
            throw new \RuntimeException('Invalid parameters.');
        }

        $model = $this->models['main'];

        // Find elastic schema
        $elasticSchemaId = $this->blocForm->getElasticSchemaId();
        $this->elasticSchema = ElasticSchema::query()
            ->andWhere(['id' => $elasticSchemaId])
            ->one();

        if ($this->elasticSchema === null) {
            throw new \RuntimeException('Block type not found.');
        }

        // Check that the schema is allowed for the model type
        $this->type = $model->getTypeQuery()->one();
        if ($this->type !== null) {
            $allowedSchemaIds = [];
            foreach ($this->type->getElasticSchemasQuery()->each() as $schema) {
                $allowedSchemaIds[] = $schema->getId();
            }
            if (!in_array($elasticSchemaId, $allowedSchemaIds, true)) {
                throw new \RuntimeException('This block type is not allowed for this element.');
            }
        }

        // Create bloc - active = false on creation (will become true after first content validation)
        $this->bloc = new Bloc();
        $this->bloc->setElasticSchemaId($this->elasticSchema->getId());
        // active defaults to false in Bloc model, don't touch it here

        // Get insert position
        $afterBlocId = $this->blocForm->getBlocAdd();
        if ($afterBlocId !== null) {
            $pivotClass = $this->getPivotClass();
            $fkColumn = $this->getPivotFkColumn();
            $pivot = $pivotClass::query()
                ->andWhere([$fkColumn => $model->getId(), 'blocId' => $afterBlocId])
                ->one();
            if ($pivot !== null) {
                $this->insertPosition = $pivot->getOrder() + 1;
            }
        }

        // Transaction: save bloc + attachBloc
        $this->beforeSave(false);
        $transaction = $model->db()->beginTransaction();
        try {
            $this->beforeSave(true);
            $this->bloc->save();
            $model->attachBloc($this->bloc, $this->insertPosition);
            $this->afterSave(true);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        $this->afterSave(false);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        return [
            'type' => OutputType::Json->value,
            'data' => [
                ...AureliaCommunication::ajaxify(
                    $this->getBlocsListId(),
                    $this->urlGenerator->generate($this->getEditRoute(), $this->extractPrimaryKeysFromModel()),
                    AjaxifyAction::Refresh,
                ),
                ...AureliaCommunication::toast(
                    $this->translator->translate('Success', category: 'dboard-common'),
                    $this->translator->translate('Block added.', category: 'dboard-common'),
                    UiColor::Success
                ),
            ],
        ];
    }
}