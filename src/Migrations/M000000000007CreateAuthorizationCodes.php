<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Migrations;

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Schema\Column\ColumnBuilder;

/**
 * Migration to create the authorizationCodes table: the codes issued by the
 * consent screen of an addon population, with their PKCE challenge.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class M000000000007CreateAuthorizationCodes implements RevertibleMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $b->createTable('{{%authorizationCodes}}', [
            'code' => ColumnBuilder::string(255)->notNull(),
            'administratorId' => ColumnBuilder::bigint()->notNull(),
            'clientId' => ColumnBuilder::string(255)->notNull(),
            'redirectUri' => ColumnBuilder::text()->notNull(),
            'scopes' => ColumnBuilder::text(),
            'expires' => ColumnBuilder::datetime()->notNull(),
            'codeChallenge' => ColumnBuilder::string(128),
            'codeChallengeMethod' => ColumnBuilder::string(16),
            'dateCreate' => ColumnBuilder::datetime()->notNull(),
        ]);

        $b->addPrimaryKey('{{%authorizationCodes}}', 'blap_authorization_codes_pk', [
            'code',
        ]);

        $b->createIndex('{{%authorizationCodes}}', 'blap_authorization_codes_admin_idx', [
            'administratorId',
        ]);

        $b->addForeignKey(
            '{{%authorizationCodes}}',
            'blap_authorization_codes_admin_fk',
            [
                'administratorId',
            ],
            '{{%administrators}}',
            [
                'id',
            ],
            'CASCADE'
        );
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('{{%authorizationCodes}}');
    }
}
