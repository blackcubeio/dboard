<?php

declare(strict_types=1);

/**
 * XeoRefresh.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Contents;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\ContentBloc;
use Blackcube\Dboard\Handlers\Commons\AbstractXeoRefresh;

/**
 * Content XEO refresh action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class XeoRefresh extends AbstractXeoRefresh
{
    protected function getModelClass(): string { return Content::class; }
    protected function getEntityName(): string { return 'content'; }
    protected function getFileRoutePrefix(): string { return 'dboard.contents'; }
    protected function getArticleBlocPivotClass(): string { return ContentBloc::class; }
    protected function getArticleBlocFkColumn(): string { return 'contentId'; }
}
