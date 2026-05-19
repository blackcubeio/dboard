<?php

declare(strict_types=1);

/**
 * Delete.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Authors;

use Blackcube\Dcore\Models\Author;
use Blackcube\Dboard\Handlers\Commons\AbstractDelete;

/**
 * Author delete action.
 */
final class Delete extends AbstractDelete
{
    protected function getModelClass(): string { return Author::class; }
    protected function getEntityName(): string { return 'author'; }
    protected function getListId(): string { return 'authors-list'; }
    protected function getListRoute(): string { return 'dboard.authors'; }
    protected function getModelName(): string
    {
        return $this->models['main']->getFirstname() . ' ' . $this->models['main']->getLastname();
    }
}
