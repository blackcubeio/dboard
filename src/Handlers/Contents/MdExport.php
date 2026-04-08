<?php

declare(strict_types=1);

/**
 * MdExport.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Contents;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dboard\Handlers\Commons\AbstractMdExport;

/**
 * Content markdown export drawer action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class MdExport extends AbstractMdExport
{
    protected function getModelClass(): string { return Content::class; }
    protected function getEntityName(): string { return 'content'; }
    protected function getEntityLabel(): string { return 'contenu'; }
    protected function getImportRouteName(): string { return 'dboard.contents.md-import'; }
}
