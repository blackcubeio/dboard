<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Administrators;

use Blackcube\Dboard\Handlers\Commons\AbstractToggle;
use Blackcube\Dboard\Models\Administrator;

/**
 * Administrator toggle action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Toggle extends AbstractToggle
{
    protected function getModelClass(): string { return Administrator::class; }
    protected function getEntityName(): string { return 'administrator'; }
    protected function getListId(): string { return 'administrators-list'; }
    protected function getListRoute(): string { return 'dboard.administrators'; }

    protected function getSuccessMessage(): string
    {
        return $this->translator->translate('Administrator "{name}" has been {status, select, enabled{enabled} other{disabled}}.', [
            'name' => $this->getModelName(),
            'status' => $this->models['main']->isActive() === true ? 'enabled' : 'disabled',
        ], 'dboard-modules');
    }
}
