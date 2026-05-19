<?php

declare(strict_types=1);

/**
 * XeoAuthors.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Contents;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\ContentAuthor;
use Blackcube\Dboard\Handlers\Commons\AbstractXeoAuthors;

/**
 * Content Xeo authors AJAX action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class XeoAuthors extends AbstractXeoAuthors
{
    protected function getModelClass(): string { return Content::class; }
    protected function getEntityName(): string { return 'content'; }
    protected function getAuthorPivotClass(): string { return ContentAuthor::class; }
    protected function getAuthorPivotFkColumn(): string { return 'contentId'; }
}
