<?php

declare(strict_types=1);

/**
 * Elastic.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Tags;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Handlers\Commons\AbstractElastic;
use Blackcube\Dboard\Models\Forms\TagForm;

/**
 * Tag elastic properties drawer action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Elastic extends AbstractElastic
{
    protected function getModelClass(): string { return Tag::class; }
    protected function getFormModelClass(): string { return TagForm::class; }
    protected function getEntityName(): string { return 'tag'; }
    protected function getFileRoutePrefix(): string { return 'dboard.tags'; }
}
