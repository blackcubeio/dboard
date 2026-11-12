<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Upgrades;

use Blackcube\Dboard\Migrations\M100000000000CreateAdministrators;
use Blackcube\Dboard\Migrations\M100000000001CreatePasskeys;
use Blackcube\Dboard\Migrations\M100000000002CreateRbacItems;
use Blackcube\Dboard\Migrations\M100000000003CreateRbacAssignments;
use Blackcube\Dboard\Migrations\M100000000004CreateRefreshTokens;
use Blackcube\Dboard\Migrations\M100000000005CreateTranslations;
use Blackcube\Dboard\Migrations\M100000000006CreateOauthClients;
use Blackcube\Dboard\Migrations\M100000000007CreateAuthorizationCodes;
use Blackcube\Dboard\Migrations\M100000000008AddMissingTranslations;
use Blackcube\Dcore\Upgrades\MigrationRenameUpgrade;

/**
 * The migrations of the package took the type digit 1 (M1 + 11 digits): a base migrated
 * under the former names gets its history rewritten.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class RenameMigrations extends MigrationRenameUpgrade
{
    protected function getRenames(): array
    {
        return [
            'Blackcube\\Dboard\\Migrations\\M000000000000CreateAdministrators' => M100000000000CreateAdministrators::class,
            'Blackcube\\Dboard\\Migrations\\M000000000001CreatePasskeys' => M100000000001CreatePasskeys::class,
            'Blackcube\\Dboard\\Migrations\\M000000000002CreateRbacItems' => M100000000002CreateRbacItems::class,
            'Blackcube\\Dboard\\Migrations\\M000000000003CreateRbacAssignments' => M100000000003CreateRbacAssignments::class,
            'Blackcube\\Dboard\\Migrations\\M000000000004CreateRefreshTokens' => M100000000004CreateRefreshTokens::class,
            'Blackcube\\Dboard\\Migrations\\M000000000005CreateTranslations' => M100000000005CreateTranslations::class,
            'Blackcube\\Dboard\\Migrations\\M000000000006CreateOauthClients' => M100000000006CreateOauthClients::class,
            'Blackcube\\Dboard\\Migrations\\M000000000007CreateAuthorizationCodes' => M100000000007CreateAuthorizationCodes::class,
            'Blackcube\\Dboard\\Migrations\\M000000000008AddMissingTranslations' => M100000000008AddMissingTranslations::class,
        ];
    }
}
