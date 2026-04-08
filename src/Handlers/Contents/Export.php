<?php

declare(strict_types=1);

/**
 * Export.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Contents;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dboard\Handlers\Commons\AbstractExport;

/**
 * Content export action.
 * Downloads a JSON export of a content with its blocs, slug, and related data.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Export extends AbstractExport
{
    protected function getModelClass(): string
    {
        return Content::class;
    }

    protected function getName(): string
    {
        return 'content';
    }
}
