<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Handler\OutputType;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Enums\UiLevel;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Blackcube\BridgeFormModel\BridgeFormModel;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * Abstract action for tags drawer.
 * Inherits from AbstractAjaxHandler and uses ActionModel configuration.
 *
 * Pipeline: setupAction() -> setupMethod() -> try { handleMethod() } catch -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractTags extends AbstractAjaxHandler
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
     * @return string The entity name (e.g., 'content')
     */
    abstract protected function getEntityName(): string;

    /**
     * Returns the pivot class name (e.g., ContentTag).
     *
     * @return string Fully qualified class name of the pivot model
     */
    abstract protected function getPivotClass(): string;

    /**
     * Returns the pivot form class name.
     *
     * @return string Fully qualified class name of the pivot form model
     */
    abstract protected function getPivotFormClass(): string;

    /**
     * Returns the pivot foreign key column name (e.g., 'contentId').
     *
     * @return string The column name
     */
    abstract protected function getPivotFkColumn(): string;

    /**
     * @var array<int, BridgeFormModel> The pivot form models for each tag
     */
    protected array $pivotForms = [];

    /**
     * @var bool Whether the save operation was successful
     */
    protected bool $saved = false;

    /**
     * @var int The number of tags saved
     */
    protected int $tagCount = 0;

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
     * Sets up the action and prepares pivot forms for all tags.
     *
     * @return ResponseInterface|null Response if setup failed, null if successful
     */
    protected function setupAction(): ?ResponseInterface
    {
        $setupResponse = parent::setupAction();
        if ($setupResponse === null) {
            $model = $this->models['main'];
            $pivotClass = $this->getPivotClass();
            $pivotFormClass = $this->getPivotFormClass();
            $fkColumn = $this->getPivotFkColumn();

            $existingPivots = [];
            $pivotsQuery = $pivotClass::query()->andWhere([
                $fkColumn => $model->getId(),
            ]);
            /** @var Pivot $pivot */
            foreach ($pivotsQuery->each() as $pivot) {
                $existingPivots[$pivot->getTagId()] = $pivot;
            }

            $tagsQuery = Tag::query()
                ->andWhere([
                    '<=',
                    'level',
                    2,
                ])
                ->natural();

            $this->pivotForms = [];
            /** @var Tag $tag */
            foreach ($tagsQuery->each() as $tag) {
                $pivot = $existingPivots[$tag->getId()] ?? null;
                $form = new $pivotFormClass();
                if ($pivot !== null) {
                    $form->initFromModel($pivot);
                    $form->setSelected(true);
                }
                $form->setTagId($tag->getId());
                $form->setTagName($tag->getName());
                $form->setTagLevel($tag->getLevel());
                $form->setTagActive($tag->isActive());
                $form->setContentId($model->getId());
                $form->setScenario('tagging');
                $this->pivotForms[] = $form;
            }
        }

        return $setupResponse;
    }

    /**
     * {@inheritdoc}
     */
    protected function setupMethod(): void
    {
        if ($this->request->getMethod() === Method::POST) {
            BridgeFormModel::loadMultiple($this->pivotForms, $this->getBodyParams());
        }
    }

    /**
     * Hook called before save.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function beforeSave(bool $inTransaction): void
    {
    }

    /**
     * Hook called after save.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function afterSave(bool $inTransaction): void
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function handleMethod(): void
    {
        if ($this->request->getMethod() === Method::POST) {
            $model = $this->models['main'];
            $pivotClass = $this->getPivotClass();
            $fkColumn = $this->getPivotFkColumn();
            $id = $model->getId();
            $pivotSetter = 'set' . ucfirst($fkColumn);

            $this->beforeSave(false);
            $transaction = $model->db()->beginTransaction();
            try {
                $this->beforeSave(true);

                $pivotModel = new $pivotClass();
                $pivotModel->deleteAll([
                    $fkColumn => $id,
                ]);

                $this->tagCount = 0;
                foreach ($this->pivotForms as $form) {
                    if ($form->getTagLevel() === 2 && $form->isSelected() === true) {
                        $pivot = new $pivotClass();
                        $pivot->{$pivotSetter}((int) $id);
                        $pivot->setTagId((int) $form->getTagId());
                        $pivot->save();
                        $this->tagCount++;
                    }
                }

                $this->afterSave(true);
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
            $this->afterSave(false);

            $this->saved = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        if ($this->saved === true) {
            $entityName = $this->getEntityName();
            $toastContent = $this->tagCount > 0
                ? $this->translator->translate('{entity, select, content{Content tagged.} tag{Tag tagged.} other{Element tagged.}}', [
                    'entity' => $entityName,
                ], 'dboard-common')
                : $this->translator->translate('{entity, select, content{Content untagged.} tag{Tag untagged.} other{Element untagged.}}', [
                    'entity' => $entityName,
                ], 'dboard-common');

            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Close),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-common'),
                        $toastContent,
                        UiLevel::Success
                    ),
                ],
            ];
        } else {
            $model = $this->models['main'];

            $content = (string) $this->renderPartial('Commons/_tags-content', [
                'model' => $model,
                'pivotForms' => $this->pivotForms,
                'pivotFkColumn' => $this->getPivotFkColumn(),
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
                    ...AureliaCommunication::dialogContent(Bleet::dialogFrame(DialogType::Drawer)
                        ->title('Tags')
                        ->primary()
                        ->content($content)
                        ->render()),
                ],
            ];
        }

        return $outputData;
    }
}