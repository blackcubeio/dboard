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
 * Creates the translation tables and seeds them from the i18n files of the package.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class M000000000005CreateTranslations implements RevertibleMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $b->createTable('{{%yii_source_message}}', [
            'id' => ColumnBuilder::bigPrimaryKey(),
            'category' => ColumnBuilder::string()->unique(false),
	        'message_id' => ColumnBuilder::text(),
	        'comment' => ColumnBuilder::text(),
        ]);
        $b->createTable('{{%yii_message}}', [
            'id' => ColumnBuilder::bigint(),
            'locale' => ColumnBuilder::string(16)->notNull()->unique(false),
            'translation' => ColumnBuilder::text(),
            'PRIMARY KEY([[id]], [[locale]])'
        ]);
        $b->addForeignKey('{{%yii_message}}', 'messages_sourceMessages_id', 'id', '{{%yii_source_message}}', 'id', 'CASCADE', 'RESTRICT');

        $this->seedTranslations($b);
    }

    private function seedTranslations(MigrationBuilder $b): void
    {
        $i18nPath = dirname(__DIR__) . '/i18n';
        $categories = [
            'dboard-common',
            'dboard-content',
            'dboard-modules',
            'dboard-onboarding',
        ];
        $locales = [
            'en',
            'de',
            'es',
            'fr',
            'it',
            'pt',
        ];

        $sourceRows = [];
        $messageMap = [];
        $id = 1;
        foreach ($categories as $category) {
            $messages = require ($i18nPath . '/en/' . $category . '.php');
            foreach ($messages as $messageId => $translation) {
                $sourceRows[] = [
                    $id,
                    $category,
                    $messageId,
                    null,
                ];
                $messageMap[$category][$messageId] = $id;
                $id++;
            }
        }
        $b->insertBatch('{{%yii_source_message}}', $sourceRows, [
            'id',
            'category',
            'message_id',
            'comment',
        ]);

        $translationRows = [];
        foreach ($locales as $locale) {
            foreach ($categories as $category) {
                $messages = require ($i18nPath . '/' . $locale . '/' . $category . '.php');
                foreach ($messages as $messageId => $translation) {
                    if (isset($messageMap[$category][$messageId]) === true) {
                        $translationRows[] = [
                            $messageMap[$category][$messageId],
                            $locale,
                            $translation,
                        ];
                    }
                }
            }
        }
        $b->insertBatch('{{%yii_message}}', $translationRows, [
            'id',
            'locale',
            'translation',
        ]);
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('{{%yii_message}}');
        $b->dropTable('{{%yii_source_message}}');
    }
}
