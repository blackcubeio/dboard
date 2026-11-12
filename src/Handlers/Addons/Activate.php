<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Addons;

/**
 * Activation (POST) of a declared, inactive add-on: migrations, registry, RBAC.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Activate extends AbstractAction
{
    protected function act(string $id): void
    {
        $this->addonActivation->activate($id);
    }

    protected function getSuccessMessage(string $name): string
    {
        return $this->translator->translate('Addon {name} activated.', [
            'name' => $name,
        ], 'dboard-modules');
    }

    protected function isOpen(string $id): bool
    {
        return $this->addonService->isDeclared($id) === true && $this->addonService->isActive($id) === false;
    }
}
