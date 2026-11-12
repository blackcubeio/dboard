<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Addons;

/**
 * Update confirmation modal (GET) of an active add-on.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class UpdateInit extends AbstractActionInit
{
    protected function getTitle(): string
    {
        return $this->translator->translate('Update addon', category: 'dboard-modules');
    }

    protected function getMessage(string $name): string
    {
        return $this->translator->translate('The migrations {name} brought since its activation will be played and its permissions synchronized.', [
            'name' => $name,
        ], 'dboard-modules');
    }

    protected function getSubmitLabel(): string
    {
        return $this->translator->translate('Update', category: 'dboard-modules');
    }

    protected function isOpen(string $id): bool
    {
        return $this->addonService->isActive($id);
    }
}
