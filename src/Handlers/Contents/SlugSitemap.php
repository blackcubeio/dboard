<?php

declare(strict_types=1);

/**
 * SlugSitemap.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Contents;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dboard\Handlers\Commons\AbstractSlugSitemap;

/**
 * Content slug/sitemap drawer action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class SlugSitemap extends AbstractSlugSitemap
{
    protected function getModelClass(): string { return Content::class; }
    protected function getEntityName(): string { return 'content'; }
    protected function getSlugGeneratorRoute(): string { return 'dboard.contents.slug-generator'; }
}
