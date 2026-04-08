<?php

declare(strict_types=1);

/**
 * Index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Authors;

use Blackcube\Dcore\Models\Author;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;

/**
 * Author index action.
 */
final class Index extends AbstractIndex
{
    protected function getModelClass(): string { return Author::class; }
    protected function getViewPrefix(): string { return 'Authors'; }
    protected function getOrderBy(): array { return ['lastname' => SORT_ASC, 'firstname' => SORT_ASC]; }
    protected function getSearchColumn(): string { return 'lastname'; }
}
