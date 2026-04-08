<?php

declare(strict_types=1);

/**
 * BlocAdd.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Tags;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dcore\Models\TagBloc;
use Blackcube\Dboard\Handlers\Commons\AbstractBlocAdd;

/**
 * Add a bloc to a tag.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class BlocAdd extends AbstractBlocAdd
{
    protected function getModelClass(): string { return Tag::class; }
    protected function getEntityName(): string { return 'tag'; }
    protected function getEditRoute(): string { return 'dboard.tags.edit'; }
    protected function getBlocsListId(): string { return 'tag-blocs-list'; }
    protected function getPivotClass(): string { return TagBloc::class; }
    protected function getPivotFkColumn(): string { return 'tagId'; }
}
