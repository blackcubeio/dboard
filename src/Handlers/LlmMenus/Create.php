<?php

declare(strict_types=1);

/**
 * Create.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\LlmMenus;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\LlmMenu;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Handlers\Commons\AbstractCreate;
use Blackcube\Dboard\Models\Forms\LlmMenuForm;

/**
 * LlmMenu create action.
 *
 * Tree: Root (level 1) → Category (level 2) → Data (level 3).
 */
final class Create extends AbstractCreate
{
    protected function getModelClass(): string { return LlmMenu::class; }
    protected function getFormModelClass(): string { return LlmMenuForm::class; }
    protected function getEntityName(): string { return 'llmMenu'; }
    protected function getViewPrefix(): string { return 'LlmMenus'; }
    protected function getListRoute(): string { return 'dboard.llmmenus'; }
    protected function getSuccessRoute(): string { return 'dboard.llmmenus.edit'; }
    protected function getMaxLevel(): int { return 3; }

    protected function getDefaultMoveTarget(): ?array
    {
        $root = LlmMenu::query()->natural()->andWhere(['level' => 1])->one();
        if ($root !== null) {
            return ['targetId' => $root->getId(), 'mode' => 'into'];
        }
        return null;
    }

    protected function beforeValidate(): void
    {
        /** @var LlmMenuForm $formModel */
        $formModel = $this->formModels['main'];

        // Calculate would-be level
        $wouldBeLevel = 1; // default: root
        if ($formModel->isMove() && $formModel->getMoveTargetId() !== null) {
            $target = LlmMenu::query()->natural()->andWhere(['id' => $formModel->getMoveTargetId()])->one();
            if ($target !== null) {
                $wouldBeLevel = match ($formModel->getMoveMode()) {
                    'into' => $target->getLevel() + 1,
                    'before', 'after' => $target->getLevel(),
                    default => $target->getLevel(),
                };
            }
        }

        $formModel->setExpectedLevel($wouldBeLevel);

        // Root and category nodes must NOT have contentId/tagId
        if ($wouldBeLevel < 3) {
            $formModel->setContentId(null);
            $formModel->setTagId(null);
        }

        // Data nodes: auto-set name from linked content or tag
        if ($wouldBeLevel >= 3 && ($formModel->getName() === '' || $formModel->getName() === null)) {
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
