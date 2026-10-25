<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\LlmMenus;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\LlmMenu;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Enums\TreePosition;
use Blackcube\Dboard\Handlers\Commons\AbstractCreate;
use Blackcube\Dboard\Models\Forms\LlmMenuForm;

/**
 * LlmMenu create action.
 *
 * Tree: Root (level 1) → Category (level 2) → Data (level 3).
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Create extends AbstractCreate
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
        $defaultMoveTarget = null;
        $root = LlmMenu::query()->natural()->andWhere([
            'level' => 1,
        ])->one();
        if ($root !== null) {
            $defaultMoveTarget = [
                'targetId' => $root->getId(),
                'mode' => TreePosition::Into->value,
            ];
        }
        return $defaultMoveTarget;
    }

    protected function beforeValidate(): void
    {
        /** @var LlmMenuForm $formModel */
        $formModel = $this->formModels['main'];

        $wouldBeLevel = 1;
        if ($formModel->isMove() === true && $formModel->getMoveTargetId() !== null) {
            $target = LlmMenu::query()->natural()->andWhere([
                'id' => $formModel->getMoveTargetId(),
            ])->one();
            $treePosition = TreePosition::tryFrom($formModel->getMoveMode());
            if ($target !== null && $treePosition !== null) {
                $wouldBeLevel = match ($treePosition) {
                    TreePosition::Into => $target->getLevel() + 1,
                    TreePosition::Before, TreePosition::After => $target->getLevel(),
                };
            }
        }

        $formModel->setExpectedLevel($wouldBeLevel);

        if (trim($formModel->getName() ?? '') === '') {
            $title = $this->linkedTitle($formModel);
            if ($title !== null) {
                $formModel->setName($title);
            }
        }
    }

    private function linkedTitle(LlmMenuForm $formModel): ?string
    {
        $linkedTitle = null;
        $linked = null;
        if ($formModel->getContentId() !== null) {
            $linked = Content::query()->andWhere([
                'id' => $formModel->getContentId(),
            ])->one();
        } elseif ($formModel->getTagId() !== null) {
            $linked = Tag::query()->andWhere([
                'id' => $formModel->getTagId(),
            ])->one();
        }
        if ($linked !== null) {
            $slug = $linked->getSlugQuery()->one();
            if ($slug !== null) {
                $xeo = $slug->getXeoQuery()->one();
                if ($xeo !== null) {
                    $title = $xeo->getTitle();
                    if ($title !== null && trim($title) !== '') {
                        $linkedTitle = $title;
                    }
                }
            }
            if ($linkedTitle === null) {
                $linkedTitle = $linked->getName();
            }
        }
        return $linkedTitle;
    }
}
