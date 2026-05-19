<?php

declare(strict_types=1);

/**
 * M000000000000CreateAdministrators.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Migrations;

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Schema\Column\ColumnBuilder;

final class M000000000000CreateAdministrators implements RevertibleMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $b->createTable('{{%administrators}}', [
            'id' => ColumnBuilder::bigPrimaryKey(),
            'firstname' => ColumnBuilder::string()->notNull(),
            'lastname' => ColumnBuilder::string()->notNull(),
            'email' => ColumnBuilder::string()->notNull()->unique(),
            'password' => ColumnBuilder::string()->notNull(),
            'locale' => ColumnBuilder::string(16),
            'active' => ColumnBuilder::boolean()->notNull()->defaultValue(true)->unique(false),
            'dateCreate' => ColumnBuilder::datetime()->notNull(),
            'dateUpdate' => ColumnBuilder::datetime(),
        ]);
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('{{%administrators}}');
    }
}
