<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Addons;

/**
 * Forget (POST) of an orphan row: removed from the registry, its tables left as they are.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Forget extends AbstractAction
{
    protected function act(string $id): void
    {
        $this->addonActivation->forget($id);
    }

    protected function getSuccessMessage(string $name): string
    {
        return $this->translator->translate('Addon {name} forgotten.', [
            'name' => $name,
        ], 'dboard-modules');
    }

    protected function isOpen(string $id): bool
    {
        return in_array($id, $this->addonService->getOrphans(), true) === true;
    }
}
