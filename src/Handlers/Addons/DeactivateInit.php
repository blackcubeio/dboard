<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Addons;

use Blackcube\Bleet\Enums\UiColor;

/**
 * Deactivation confirmation modal (GET) of an active add-on: it names the add-on and says that
 * its tables and their data are deleted.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class DeactivateInit extends AbstractActionInit
{
    protected function getTitle(): string
    {
        return $this->translator->translate('Deactivate addon', category: 'dboard-modules');
    }

    protected function getMessage(string $name): string
    {
        return $this->translator->translate('{name} will be deactivated: its tables and their data are deleted, its permissions and OAuth2 clients removed.', [
            'name' => $name,
        ], 'dboard-modules');
    }

    protected function getSubmitLabel(): string
    {
        return $this->translator->translate('Deactivate', category: 'dboard-common');
    }

    protected function getColor(): UiColor
    {
        return UiColor::Danger;
    }

    protected function isOpen(string $id): bool
    {
        return $this->addonService->isActive($id);
    }
}
