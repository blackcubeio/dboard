<?php

declare(strict_types=1);

/**
 * M000000000004CreateRefreshTokens.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Migrations;

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Schema\Column\ColumnBuilder;

final class M000000000004CreateRefreshTokens implements RevertibleMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $b->createTable('{{%refreshTokens}}', [
            'token' => ColumnBuilder::string(255)->notNull(),
            'administratorId' => ColumnBuilder::bigint()->notNull(),
            'clientId' => ColumnBuilder::string(255)->notNull(),
            'scopes' => ColumnBuilder::text(),
            'expires' => ColumnBuilder::datetime()->notNull(),
            'revoked' => ColumnBuilder::boolean()->notNull()->defaultValue(false),
            'dateCreate' => ColumnBuilder::datetime()->notNull(),
        ]);

        $b->addPrimaryKey('{{%refreshTokens}}', 'blap_refresh_tokens_pk', ['token']);

        $b->createIndex('{{%refreshTokens}}', 'blap_refresh_tokens_admin_idx', ['administratorId']);

        $b->addForeignKey(
            '{{%refreshTokens}}',
            'blap_refresh_tokens_admin_fk',
            ['administratorId'],
            '{{%administrators}}',
            ['id'],
            'CASCADE'
        );
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('{{%refreshTokens}}');
    }
}
