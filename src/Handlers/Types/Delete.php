<?php

declare(strict_types=1);

/**
 * Delete.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Types;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dcore\Models\Type;
use Blackcube\Dboard\Handlers\Commons\AbstractDelete;

/**
 * Type delete action (DELETE only).
 * Blocks deletion if type is used by Content or Tag.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Delete extends AbstractDelete
{
    protected function getModelClass(): string { return Type::class; }
    protected function getEntityName(): string { return 'type'; }
    protected function getEntityLabel(): string { return 'type'; }
    protected function getListId(): string { return 'types-list'; }
    protected function getListRoute(): string { return 'dboard.types'; }

    /**
     * Check if type is used by Content or Tag.
     *
     * @return array{contents: int, tags: int} Usage counts
     */
    private function getUsageCounts(): array
    {
        $typeId = $this->models['main']->getId();
        return [
            'contents' => Content::query()->andWhere(['typeId' => $typeId])->count(),
            'tags' => Tag::query()->andWhere(['typeId' => $typeId])->count(),
        ];
    }

    protected function beforeDelete(bool $inTransaction): void
    {
        if ($inTransaction) {
            return;
        }

        $usage = $this->getUsageCounts();
        if ($usage['contents'] > 0 || $usage['tags'] > 0) {
            throw new \RuntimeException('Cannot delete type. It is used by ' . $usage['contents'] . ' content(s) and ' . $usage['tags'] . ' tag(s).');
        }
    }
}