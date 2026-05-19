<?php

declare(strict_types=1);

/**
 * XeoAuthors.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Tags;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dcore\Models\TagAuthor;
use Blackcube\Dboard\Handlers\Commons\AbstractXeoAuthors;

/**
 * Tag Xeo authors AJAX action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class XeoAuthors extends AbstractXeoAuthors
{
    protected function getModelClass(): string { return Tag::class; }
    protected function getEntityName(): string { return 'tag'; }
    protected function getAuthorPivotClass(): string { return TagAuthor::class; }
    protected function getAuthorPivotFkColumn(): string { return 'tagId'; }
}
