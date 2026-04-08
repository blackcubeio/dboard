<?php

declare(strict_types=1);

/**
 * MdExport.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Tags;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Handlers\Commons\AbstractMdExport;

/**
 * Tag markdown export drawer action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class MdExport extends AbstractMdExport
{
    protected function getModelClass(): string { return Tag::class; }
    protected function getEntityName(): string { return 'tag'; }
    protected function getEntityLabel(): string { return 'category'; }
    protected function getImportRouteName(): string { return 'dboard.tags.md-import'; }
}
