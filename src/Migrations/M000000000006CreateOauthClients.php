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
 * Migration to create the oauthClients table: the public clients registered
 * dynamically on an addon population.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class M000000000006CreateOauthClients implements RevertibleMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $b->createTable('{{%oauthClients}}', [
            'id' => ColumnBuilder::string(64)->notNull(),
            'population' => ColumnBuilder::string(64)->notNull(),
            'name' => ColumnBuilder::string(255)->notNull(),
            'redirectUris' => ColumnBuilder::json()->notNull(),
            'grants' => ColumnBuilder::json()->notNull(),
            'dateCreate' => ColumnBuilder::datetime()->notNull(),
        ]);

        $b->addPrimaryKey('{{%oauthClients}}', 'blap_oauth_clients_pk', [
            'id',
        ]);

        $b->createIndex('{{%oauthClients}}', 'blap_oauth_clients_population_idx', [
            'population',
        ]);
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('{{%oauthClients}}');
    }
}
