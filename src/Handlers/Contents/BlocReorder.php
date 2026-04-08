<?php

declare(strict_types=1);

/**
 * BlocReorder.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Contents;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dboard\Handlers\Commons\AbstractBlocReorder;

/**
 * Reorder blocs of a content.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class BlocReorder extends AbstractBlocReorder
{
    protected function getModelClass(): string { return Content::class; }
    protected function getEntityName(): string { return 'content'; }
    protected function getEditRoute(): string { return 'dboard.contents.edit'; }
    protected function getBlocsListId(): string { return 'content-blocs-list'; }
}
