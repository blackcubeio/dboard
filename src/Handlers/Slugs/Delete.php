<?php

declare(strict_types=1);

/**
 * Delete.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Slugs;

use Blackcube\Dcore\Models\Slug;
use Blackcube\Dboard\Handlers\Commons\AbstractDelete;

/**
 * Slug delete action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Delete extends AbstractDelete
{
    protected function getModelClass(): string { return Slug::class; }
    protected function getEntityName(): string { return 'slug'; }
    protected function getEntityLabel(): string { return 'redirection'; }
    protected function getListId(): string { return 'slugs-list'; }
    protected function getListRoute(): string { return 'dboard.slugs'; }

    protected function getModelName(): string
    {
        return $this->models['main']->getPath();
    }
}
