<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Addons;

/**
 * Update (POST) of an active add-on: the migrations a newer package brought, the RBAC.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Update extends AbstractAction
{
    protected function act(string $id): void
    {
        $this->addonActivation->update($id);
    }

    protected function getSuccessMessage(string $name): string
    {
        return $this->translator->translate('Addon {name} updated.', [
            'name' => $name,
        ], 'dboard-modules');
    }

    protected function isOpen(string $id): bool
    {
        return $this->addonService->isActive($id);
    }
}
