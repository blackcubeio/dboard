<?php

declare(strict_types=1);

/**
 * Toggle.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Languages;

use Blackcube\Dcore\Models\Language;
use Blackcube\Dboard\Handlers\Commons\AbstractToggle;

/**
 * Language toggle action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Toggle extends AbstractToggle
{
    protected function getModelClass(): string { return Language::class; }
    protected function getEntityName(): string { return 'language'; }
    protected function getListId(): string { return 'languages-list'; }
    protected function getListRoute(): string { return 'dboard.languages'; }
}
