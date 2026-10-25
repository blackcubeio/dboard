<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Menus;

use Blackcube\Dcore\Models\Host;
use Blackcube\Dcore\Models\Menu;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;

/**
 * Menus index action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Index extends AbstractIndex
{
    protected function getModelClass(): string { return Menu::class; }
    protected function getViewPrefix(): string { return 'Menus'; }

    /**
     * Eager-loads the host for the current page, removing the per-row N+1.
     *
     * @return array<int, Menu> The current page menus, host preloaded
     */
    protected function eagerLoad(): array
    {
        $items = [];
        $hostIds = [];
        foreach ($this->paginator->read() as $menu) {
            /** @var Menu $menu */
            $items[] = $menu;
            if ($menu->getHostId() !== null) {
                $hostIds[$menu->getHostId()] = $menu->getHostId();
            }
        }

        $hosts = $this->getIndexedRecords(Host::class, $hostIds);

        foreach ($items as $menu) {
            $hostId = $menu->getHostId();
            $menu->populateRelation('host', $hostId !== null ? ($hosts[$hostId] ?? null) : null);
        }

        return $items;
    }
}
