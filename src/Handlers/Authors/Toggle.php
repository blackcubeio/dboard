<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Authors;

use Blackcube\Dcore\Models\Author;
use Blackcube\Dboard\Handlers\Commons\AbstractToggle;

/**
 * Author toggle action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Toggle extends AbstractToggle
{
    protected function getModelClass(): string { return Author::class; }
    protected function getEntityName(): string { return 'author'; }
    protected function getListId(): string { return 'authors-list'; }
    protected function getListRoute(): string { return 'dboard.authors'; }

    protected function getSuccessMessage(): string
    {
        return $this->translator->translate('Author "{name}" has been {status, select, enabled{enabled} other{disabled}}.', [
            'name' => $this->getModelName(),
            'status' => $this->models['main']->isActive() === true ? 'enabled' : 'disabled',
        ], 'dboard-modules');
    }

    protected function getModelName(): string
    {
        return $this->models['main']->getFirstname() . ' ' . $this->models['main']->getLastname();
    }
}
