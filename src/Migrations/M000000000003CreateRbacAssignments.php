<?php

declare(strict_types=1);

/**
 * M000000000003CreateRbacAssignments.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Migrations;

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Schema\Column\ColumnBuilder;

final class M000000000003CreateRbacAssignments implements RevertibleMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $b->createTable('{{%yii_rbac_assignment}}', [
            'item_name' => ColumnBuilder::string(126)->notNull(),
            'user_id' => ColumnBuilder::string(126)->notNull(),
            'created_at' => ColumnBuilder::integer()->notNull(),
            'PRIMARY KEY([[item_name]], [[user_id]])',
        ]);
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('{{%yii_rbac_assignment}}');
    }
}
