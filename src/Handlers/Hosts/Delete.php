<?php

declare(strict_types=1);

/**
 * Delete.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Hosts;

use Blackcube\Dcore\Models\Host;
use Blackcube\Dcore\Models\Menu;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dboard\Handlers\Commons\AbstractDelete;

/**
 * Host delete action (DELETE only).
 * Host id=1 is protected and cannot be deleted.
 * Blocks deletion if host is used by Slug or Menu.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Delete extends AbstractDelete
{
    protected function getModelClass(): string { return Host::class; }
    protected function getEntityName(): string { return 'host'; }
    protected function getEntityLabel(): string { return 'host'; }
    protected function getListId(): string { return 'hosts-list'; }
    protected function getListRoute(): string { return 'dboard.hosts'; }

    /**
     * Check if host is used by Slug or Menu.
     *
     * @return array{slugs: int, menus: int} Usage counts
     */
    private function getUsageCounts(): array
    {
        $hostId = $this->models['main']->getId();
        return [
            'slugs' => Slug::query()->andWhere(['hostId' => $hostId])->count(),
            'menus' => Menu::query()->andWhere(['hostId' => $hostId])->count(),
        ];
    }

    protected function beforeDelete(bool $inTransaction): void
    {
        if ($inTransaction) {
            return;
        }

        if ($this->models['main']->getId() === 1) {
            throw new \RuntimeException('The default host cannot be deleted.');
        }

        $usage = $this->getUsageCounts();
        if ($usage['slugs'] > 0 || $usage['menus'] > 0) {
            throw new \RuntimeException('Cannot delete host. It is used by ' . $usage['slugs'] . ' slug(s) and ' . $usage['menus'] . ' menu(s).');
        }
    }
}