<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Addons;

/**
 * Deactivation (POST) of an active add-on: migrations reverted, registry, OAuth2 clients, RBAC.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Deactivate extends AbstractAction
{
    protected function act(string $id): void
    {
        $this->addonActivation->deactivate($id);
    }

    protected function getSuccessMessage(string $name): string
    {
        return $this->translator->translate('Addon {name} deactivated.', [
            'name' => $name,
        ], 'dboard-modules');
    }

    protected function isOpen(string $id): bool
    {
        return $this->addonService->isActive($id);
    }
}
