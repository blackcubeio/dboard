<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Commands\AddonActivateCommand;
use Blackcube\Dboard\Commands\AddonDeactivateCommand;
use Blackcube\Dboard\Commands\AddonListCommand;
use Blackcube\Dboard\Commands\AddonUpdateCommand;
use Blackcube\Dboard\Commands\RefreshAaguidCommand;

return [
    'dboard:refreshAaguid' => RefreshAaguidCommand::class,
    'dboard:addonList' => AddonListCommand::class,
    'dboard:addonActivate' => AddonActivateCommand::class,
    'dboard:addonUpdate' => AddonUpdateCommand::class,
    'dboard:addonDeactivate' => AddonDeactivateCommand::class,
];
