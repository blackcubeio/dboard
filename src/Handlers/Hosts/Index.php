<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Hosts;

use Blackcube\Dcore\Models\Host;
use Blackcube\Dcore\Models\Menu;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;

/**
 * Hosts index action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Index extends AbstractIndex
{
    /** @var array<int, true> Hosts of the current page used by a slug or a menu */
    protected array $usedHostIds = [];

    protected function getModelClass(): string { return Host::class; }
    protected function getViewPrefix(): string { return 'Hosts'; }
    protected function getOrderBy(): array { return [
        'name' => SORT_ASC,
    ]; }
    protected function getSearchColumn(): string { return 'name'; }

    /**
     * Reads the current page and finds, in one query per table, the hosts used by a slug or a menu.
     *
     * @return array<int, Host> The current page hosts
     */
    protected function eagerLoad(): array
    {
        $items = [];
        $hostIds = [];
        foreach ($this->paginator->read() as $host) {
            /** @var Host $host */
            $items[] = $host;
            $hostIds[] = $host->getId();
        }

        $this->usedHostIds = [];
        if (empty($hostIds) === false) {
            $slugHostIdsQuery = Slug::query()
                ->select('hostId')
                ->distinct()
                ->andWhere([
                    'in',
                    'hostId',
                    $hostIds,
                ]);
            foreach ($slugHostIdsQuery->column() as $usedHostId) {
                $this->usedHostIds[(int) $usedHostId] = true;
            }
            $menuHostIdsQuery = Menu::query()
                ->select('hostId')
                ->distinct()
                ->andWhere([
                    'in',
                    'hostId',
                    $hostIds,
                ]);
            foreach ($menuHostIdsQuery->column() as $usedHostId) {
                $this->usedHostIds[(int) $usedHostId] = true;
            }
        }

        return $items;
    }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();
        $outputData['data']['usedHostIds'] = $this->usedHostIds;
        return $outputData;
    }
}
