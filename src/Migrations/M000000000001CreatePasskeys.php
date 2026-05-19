<?php

declare(strict_types=1);

/**
 * M000000000001CreatePasskeys.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Migrations;

use Blackcube\Dboard\Services\PasskeyService;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Schema\Column\ColumnBuilder;

final class M000000000001CreatePasskeys implements RevertibleMigrationInterface
{
    public function __construct(
        private PasskeyService $passkeyService,
    ) {}

    public function up(MigrationBuilder $b): void
    {
        $b->createTable('{{%passkeyDevices}}', [
            'aaguid' => ColumnBuilder::string()->notNull(),
            'name' => ColumnBuilder::string()->notNull(),
            'iconLight' => ColumnBuilder::boolean()->notNull()->defaultValue(false),
            'iconDark' => ColumnBuilder::boolean()->notNull()->defaultValue(false),
            'dateCreate' => ColumnBuilder::datetime()->notNull(),
            'dateUpdate' => ColumnBuilder::datetime(),
            'PRIMARY KEY([[aaguid]])',
        ]);

        $b->createTable('{{%passkeys}}', [
            'id' => ColumnBuilder::string()->notNull(),
            'name' => ColumnBuilder::string()->notNull(),
            'administratorId' => ColumnBuilder::bigint()->notNull()->unique(false),
            'type' => ColumnBuilder::string()->notNull(),
            'attestationType' => ColumnBuilder::string()->notNull(),
            'aaguid' => ColumnBuilder::string(),
            'credentialPublicKey' => ColumnBuilder::text()->notNull(),
            'userHandle' => ColumnBuilder::string()->notNull()->unique(false),
            'counter' => ColumnBuilder::integer()->notNull()->defaultValue(0),
            'jsonData' => ColumnBuilder::text(),
            'active' => ColumnBuilder::boolean()->notNull()->defaultValue(true)->unique(false),
            'dateCreate' => ColumnBuilder::datetime()->notNull(),
            'dateUpdate' => ColumnBuilder::datetime(),
            'PRIMARY KEY([[id]])',
        ]);

        $b->addForeignKey(
            '{{%passkeys}}',
            'fk_passkeys_administratorId',
            ['[[administratorId]]'],
            '{{%administrators}}',
            ['[[id]]'],
            'CASCADE',
            'CASCADE'
        );

        $b->addForeignKey(
            '{{%passkeys}}',
            'fk_passkeys_aaguid',
            ['[[aaguid]]'],
            '{{%passkeyDevices}}',
            ['[[aaguid]]'],
            'SET NULL',
            'CASCADE'
        );

        $this->passkeyService->importAaguid();
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('{{%passkeys}}');
        $b->dropTable('{{%passkeyDevices}}');
        $this->passkeyService->cleanIcons();
    }
}
