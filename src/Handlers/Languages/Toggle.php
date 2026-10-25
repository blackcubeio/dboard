<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Languages;

use Blackcube\Dcore\Models\Language;
use Blackcube\Dboard\Handlers\Commons\AbstractToggle;

/**
 * Language toggle action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Toggle extends AbstractToggle
{
    protected function getModelClass(): string { return Language::class; }
    protected function getEntityName(): string { return 'language'; }
    protected function getListId(): string { return 'languages-list'; }
    protected function getListRoute(): string { return 'dboard.languages'; }

    protected function getSuccessMessage(): string
    {
        return $this->translator->translate('Language "{name}" has been {status, select, enabled{enabled} other{disabled}}.', [
            'name' => $this->getModelName(),
            'status' => $this->models['main']->isActive() === true ? 'enabled' : 'disabled',
        ], 'dboard-modules');
    }
}
