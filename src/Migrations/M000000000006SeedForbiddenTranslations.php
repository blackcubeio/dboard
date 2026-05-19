<?php

declare(strict_types=1);

/**
 * M000000000006SeedForbiddenTranslations.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Migrations;

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;

final class M000000000006SeedForbiddenTranslations implements RevertibleMigrationInterface
{
    private const CATEGORY = 'dboard-common';

    private const KEYS = [
        'Access denied',
        'Your account does not have permission to access this section.',
        'Back to login',
    ];

    public function up(MigrationBuilder $b): void
    {
        $i18nPath = dirname(__DIR__) . '/i18n';
        $enFile = $i18nPath . '/en/' . self::CATEGORY . '.php';
        if (file_exists($enFile) === false) {
            return;
        }

        foreach (self::KEYS as $key) {
            $b->delete('{{%yii_source_message}}', ['category' => self::CATEGORY, 'message_id' => $key]);
        }

        $enMessages = require $enFile;
        $messageMap = [];
        foreach (self::KEYS as $key) {
            if (isset($enMessages[$key]) === false) {
                continue;
            }
            $b->insert('{{%yii_source_message}}', [
                'category' => self::CATEGORY,
                'message_id' => $key,
            ]);
            $messageMap[$key] = (int) $b->getDb()->getLastInsertID();
        }

        $translationRows = [];
        foreach (glob($i18nPath . '/*', GLOB_ONLYDIR) as $dir) {
            $locale = basename($dir);
            $file = $i18nPath . '/' . $locale . '/' . self::CATEGORY . '.php';
            if (file_exists($file) === false) {
                continue;
            }
            $localeMessages = require $file;
            foreach (self::KEYS as $key) {
                if (isset($messageMap[$key]) === false || isset($localeMessages[$key]) === false) {
                    continue;
                }
                $translationRows[] = [$messageMap[$key], $locale, $localeMessages[$key]];
            }
        }

        if ($translationRows !== []) {
            $b->batchInsert('{{%yii_message}}', ['id', 'locale', 'translation'], $translationRows);
        }
    }

    public function down(MigrationBuilder $b): void
    {
        foreach (self::KEYS as $key) {
            $b->delete('{{%yii_source_message}}', ['category' => self::CATEGORY, 'message_id' => $key]);
        }
    }
}
