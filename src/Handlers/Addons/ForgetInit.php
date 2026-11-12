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
 * Forget confirmation modal (GET) of an orphan row: an add-on whose package is gone.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ForgetInit extends AbstractActionInit
{
    protected function getTitle(): string
    {
        return $this->translator->translate('Forget addon', category: 'dboard-modules');
    }

    protected function getMessage(string $name): string
    {
        return $this->translator->translate('The row of {name} will be removed from the registry; its tables, if any, are left as they are.', [
            'name' => $name,
        ], 'dboard-modules');
    }

    protected function getSubmitLabel(): string
    {
        return $this->translator->translate('Forget', category: 'dboard-modules');
    }

    protected function getColor(): UiColor
    {
        return UiColor::Danger;
    }

    protected function isOpen(string $id): bool
    {
        return in_array($id, $this->addonService->getOrphans(), true) === true;
    }
}
