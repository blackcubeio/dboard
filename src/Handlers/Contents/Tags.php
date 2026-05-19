<?php

declare(strict_types=1);

/**
 * Tags.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Contents;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\ContentTag;
use Blackcube\Dboard\Handlers\Commons\AbstractTags;
use Blackcube\Dboard\Models\Forms\ContentTagForm;

/**
 * Content tags drawer action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Tags extends AbstractTags
{
    protected function getModelClass(): string { return Content::class; }
    protected function getEntityName(): string { return 'content'; }
    protected function getPivotClass(): string { return ContentTag::class; }
    protected function getPivotFormClass(): string { return ContentTagForm::class; }
    protected function getPivotFkColumn(): string { return 'contentId'; }
}
