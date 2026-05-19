<?php

declare(strict_types=1);

/**
 * M000000000002CreateRbacItems.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Migrations;

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Schema\Column\ColumnBuilder;

final class M000000000002CreateRbacItems implements RevertibleMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $b->createTable('{{%yii_rbac_item}}', [
            'name' => ColumnBuilder::string(126)->notNull(),
            'type' => ColumnBuilder::string(10)->notNull()->unique(false),
            'description' => ColumnBuilder::string(191),
            'rule_name' => ColumnBuilder::string(64),
            'created_at' => ColumnBuilder::integer()->notNull(),
            'updated_at' => ColumnBuilder::integer()->notNull(),
            'PRIMARY KEY([[name]])',
        ]);

        $b->createTable('{{%yii_rbac_item_child}}', [
            'parent' => ColumnBuilder::string(126)->notNull(),
            'child' => ColumnBuilder::string(126)->notNull(),
            'PRIMARY KEY([[parent]], [[child]])',
        ]);

        $b->addForeignKey('{{%yii_rbac_item_child}}', 'yii_rbac_item_child__parent_fk', ['parent'], '{{%yii_rbac_item}}', ['name'], 'CASCADE');
        $b->addForeignKey('{{%yii_rbac_item_child}}', 'yii_rbac_item_child__child_fk', ['child'], '{{%yii_rbac_item}}', ['name'], 'CASCADE');
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('{{%yii_rbac_item_child}}');
        $b->dropTable('{{%yii_rbac_item}}');
    }
}
