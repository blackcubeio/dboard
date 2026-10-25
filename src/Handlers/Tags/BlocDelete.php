<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Tags;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dcore\Models\TagBloc;
use Blackcube\Dboard\Handlers\Commons\AbstractBlocDelete;

/**
 * Delete a bloc from a tag.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class BlocDelete extends AbstractBlocDelete
{
    protected function getModelClass(): string { return Tag::class; }
    protected function getPivotClass(): string { return TagBloc::class; }
    protected function getEntityName(): string { return 'tag'; }
    protected function getPivotFkColumn(): string { return 'tagId'; }
    protected function getEditRoute(): string { return 'dboard.tags.edit'; }
    protected function getBlocsListId(): string { return 'tag-blocs-list'; }
}
