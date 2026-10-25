<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Slugs;

use Blackcube\Dcore\Models\Slug;
use Blackcube\Dboard\Handlers\Commons\AbstractToggle;

/**
 * Slug toggle action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Toggle extends AbstractToggle
{
    protected function getModelClass(): string { return Slug::class; }
    protected function getEntityName(): string { return 'slug'; }
    protected function getListId(): string { return 'slugs-list'; }
    protected function getListRoute(): string { return 'dboard.slugs'; }

    protected function getSuccessMessage(): string
    {
        return $this->translator->translate('Slug "{name}" has been {status, select, enabled{enabled} other{disabled}}.', [
            'name' => $this->getModelName(),
            'status' => $this->models['main']->isActive() === true ? 'enabled' : 'disabled',
        ], 'dboard-modules');
    }

    protected function getModelName(): string
    {
        return $this->models['main']->getPath();
    }
}
