<?php

declare(strict_types=1);

/**
 * BlocReorder.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Tags;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Handlers\Commons\AbstractBlocReorder;

/**
 * Reorder blocs of a tag.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class BlocReorder extends AbstractBlocReorder
{
    protected function getModelClass(): string { return Tag::class; }
    protected function getEntityName(): string { return 'tag'; }
    protected function getEditRoute(): string { return 'dboard.tags.edit'; }
    protected function getBlocsListId(): string { return 'tag-blocs-list'; }
}
