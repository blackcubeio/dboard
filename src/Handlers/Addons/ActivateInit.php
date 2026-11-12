<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Addons;

/**
 * Activation confirmation modal (GET) of a declared, inactive add-on.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ActivateInit extends AbstractActionInit
{
    protected function getTitle(): string
    {
        return $this->translator->translate('Activate addon', category: 'dboard-modules');
    }

    protected function getMessage(string $name): string
    {
        return $this->translator->translate('The migrations of {name} will be played and its permissions added to the roles.', [
            'name' => $name,
        ], 'dboard-modules');
    }

    protected function getSubmitLabel(): string
    {
        return $this->translator->translate('Activate', category: 'dboard-common');
    }

    protected function isOpen(string $id): bool
    {
        return $this->addonService->isDeclared($id) === true && $this->addonService->isActive($id) === false;
    }
}
