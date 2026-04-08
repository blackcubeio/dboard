<?php

declare(strict_types=1);

/**
 * Edit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\LlmMenus;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\LlmMenu;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Handlers\Commons\AbstractEdit;
use Blackcube\Dboard\Models\Forms\LlmMenuForm;

/**
 * LlmMenu edit action.
 *
 * Tree: Root (level 1) → Category (level 2) → Data (level 3).
 */
final class Edit extends AbstractEdit
{
    protected function getModelClass(): string { return LlmMenu::class; }
    protected function getFormModelClass(): string { return LlmMenuForm::class; }
    protected function getEntityName(): string { return 'llmMenu'; }
    protected function getViewPrefix(): string { return 'LlmMenus'; }
    protected function getListRoute(): string { return 'dboard.llmmenus'; }
    protected function getMaxLevel(): int { return 3; }

    protected function beforeValidate(): void
    {
        /** @var LlmMenuForm $formModel */
        $formModel = $this->formModels['main'];
        /** @var LlmMenu $model */
        $model = $this->models['main'];

        $currentLevel = $model->getLevel();

        // Calculate target level after potential move
        $targetLevel = $currentLevel;
        if ($formModel->isMove() && $formModel->getMoveTargetId() !== null) {
            $target = LlmMenu::query()->natural()->andWhere(['id' => $formModel->getMoveTargetId()])->one();
            if ($target !== null) {
                $targetLevel = match ($formModel->getMoveMode()) {
                    'into' => $target->getLevel() + 1,
                    'before', 'after' => $target->getLevel(),
                    default => $target->getLevel(),
                };
            }
        }

        // Validate based on current level (form shows/hides sections by current level)
        $formModel->setExpectedLevel($currentLevel);

        // Null out links if target level < 3 (root or category after move)
        if ($targetLevel < 3) {
            $formModel->setContentId(null);
            $formModel->setTagId(null);
        }

        // Data nodes: auto-set name from linked content or tag
        if ($currentLevel >= 3) {
            if ($formModel->getContentId() !== null) {
                $linked = Content::query()->andWhere(['id' => $formModel->getContentId()])->one();
                if ($linked !== null) {
                    $formModel->setName($linked->getName());
                }
            } elseif ($formModel->getTagId() !== null) {
                $linked = Tag::query()->andWhere(['id' => $formModel->getTagId()])->one();
                if ($linked !== null) {
                    $formModel->setName($linked->getName());
                }
            }
        }
    }
}
