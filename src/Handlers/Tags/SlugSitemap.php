<?php

declare(strict_types=1);

/**
 * SlugSitemap.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Tags;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Handlers\Commons\AbstractSlugSitemap;

/**
 * Tag slug/sitemap drawer action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class SlugSitemap extends AbstractSlugSitemap
{
    protected function getModelClass(): string { return Tag::class; }
    protected function getEntityName(): string { return 'tag'; }
    protected function getSlugGeneratorRoute(): string { return 'dboard.tags.slug-generator'; }
}
