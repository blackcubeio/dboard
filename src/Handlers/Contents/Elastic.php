<?php

declare(strict_types=1);

/**
 * Elastic.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Contents;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dboard\Handlers\Commons\AbstractElastic;
use Blackcube\Dboard\Models\Forms\ContentForm;

/**
 * Content elastic properties drawer action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Elastic extends AbstractElastic
{
    protected function getModelClass(): string { return Content::class; }
    protected function getFormModelClass(): string { return ContentForm::class; }
    protected function getEntityName(): string { return 'content'; }
    protected function getFileRoutePrefix(): string { return 'dboard.contents'; }
}
