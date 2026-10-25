<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Contents;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dboard\Handlers\Commons\AbstractDelete;

/**
 * Content delete action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Delete extends AbstractDelete
{
    protected function getModelClass(): string { return Content::class; }
    protected function getEntityName(): string { return 'content'; }
    protected function getListId(): string { return 'contents-list'; }
    protected function getListRoute(): string { return 'dboard.contents'; }
    protected function getSuccessMessage(): string { return $this->translator->translate('Content "{name}" has been deleted.', [
        'name' => $this->getModelName(),
    ], 'dboard-content'); }
}
