<?php

declare(strict_types=1);

/**
 * Xeo.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Tags;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dcore\Models\TagAuthor;
use Blackcube\Dcore\Models\TagBloc;
use Blackcube\Dboard\Handlers\Commons\AbstractXeo;

/**
 * Tag Xeo drawer action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Xeo extends AbstractXeo
{
    protected function getModelClass(): string { return Tag::class; }
    protected function getEntityName(): string { return 'tag'; }
    protected function getFileRoutePrefix(): string { return 'dboard.tags'; }
    protected function getArticleBlocPivotClass(): string { return TagBloc::class; }
    protected function getArticleBlocFkColumn(): string { return 'tagId'; }
    protected function getAuthorPivotClass(): string { return TagAuthor::class; }
    protected function getAuthorPivotFkColumn(): string { return 'tagId'; }
    protected function getLlmMenuFkColumn(): string { return 'tagId'; }
}
