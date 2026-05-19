<?php

declare(strict_types=1);

/**
 * Index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Hosts;

use Blackcube\Dcore\Models\Host;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;

/**
 * Hosts index action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Index extends AbstractIndex
{
    protected function getModelClass(): string { return Host::class; }
    protected function getViewPrefix(): string { return 'Hosts'; }
    protected function getOrderBy(): array { return ['name' => SORT_ASC]; }
    protected function getSearchColumn(): string { return 'name'; }
}
