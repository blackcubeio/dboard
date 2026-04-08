<?php

declare(strict_types=1);

/**
 * BlocAdd.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Contents;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\ContentBloc;
use Blackcube\Dboard\Handlers\Commons\AbstractBlocAdd;

/**
 * Add a bloc to a content.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class BlocAdd extends AbstractBlocAdd
{
    protected function getModelClass(): string { return Content::class; }
    protected function getEntityName(): string { return 'content'; }
    protected function getEditRoute(): string { return 'dboard.contents.edit'; }
    protected function getBlocsListId(): string { return 'content-blocs-list'; }
    protected function getPivotClass(): string { return ContentBloc::class; }
    protected function getPivotFkColumn(): string { return 'contentId'; }
}
