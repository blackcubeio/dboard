<?php

declare(strict_types=1);

/**
 * XeoRefresh.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Tags;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dcore\Models\TagBloc;
use Blackcube\Dboard\Handlers\Commons\AbstractXeoRefresh;

/**
 * Tag XEO refresh action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class XeoRefresh extends AbstractXeoRefresh
{
    protected function getModelClass(): string { return Tag::class; }
    protected function getEntityName(): string { return 'tag'; }
    protected function getFileRoutePrefix(): string { return 'dboard.tags'; }
    protected function getArticleBlocPivotClass(): string { return TagBloc::class; }
    protected function getArticleBlocFkColumn(): string { return 'tagId'; }
}
