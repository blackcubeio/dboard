<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Tags;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Handlers\Commons\AbstractDelete;

/**
 * Tag delete action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Delete extends AbstractDelete
{
    protected function getModelClass(): string { return Tag::class; }
    protected function getEntityName(): string { return 'tag'; }
    protected function getListId(): string { return 'tags-list'; }
    protected function getListRoute(): string { return 'dboard.tags'; }
    protected function getSuccessMessage(): string { return $this->translator->translate('Tag "{name}" has been deleted.', [
        'name' => $this->getModelName(),
    ], 'dboard-content'); }
}
