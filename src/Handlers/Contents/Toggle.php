<?php

declare(strict_types=1);

/**
 * Toggle.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Contents;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dboard\Handlers\Commons\AbstractToggle;

/**
 * Content toggle action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Toggle extends AbstractToggle
{
    protected function getModelClass(): string { return Content::class; }
    protected function getEntityName(): string { return 'content'; }
    protected function getListId(): string { return 'contents-list'; }
    protected function getListRoute(): string { return 'dboard.contents'; }
}
