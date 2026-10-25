<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Tags;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Handlers\Commons\AbstractExport;

/**
 * Tag export action.
 * Downloads a JSON export of a tag with its blocs, slug, and related data.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Export extends AbstractExport
{
    protected function getModelClass(): string
    {
        return Tag::class;
    }

    protected function getName(): string
    {
        return 'tag';
    }
}
