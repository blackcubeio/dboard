<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Migrations;

use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\MigrationInterface;
use Yiisoft\Db\Query\Query;

/**
 * Adds to the translation tables the keys the i18n files hold and the
 * database does not yet: a base installed before them gets them, a base
 * installed from now on already has them from CreateTranslations. Not
 * revertible: the keys it adds cannot be told apart later from the ones seeded
 * at installation.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class M000000000008AddMissingTranslations implements MigrationInterface
{
    public const TABLE_SOURCE = '{{%yii_source_message}}';
    public const TABLE_MESSAGE = '{{%yii_message}}';

    private const CATEGORIES = [
        'dboard-common',
        'dboard-content',
        'dboard-modules',
        'dboard-onboarding',
    ];

    private const LOCALES = [
        'en',
        'de',
        'es',
        'fr',
        'it',
        'pt',
    ];

    public function up(MigrationBuilder $b): void
    {
        $i18nPath = dirname(__DIR__) . '/i18n';

        $knownKeys = [];
        $knownKeysQuery = (new Query($b->getDb()))
            ->select([
                'category',
                'message_id',
            ])
            ->from(self::TABLE_SOURCE)
            ->andWhere([
                'category' => self::CATEGORIES,
            ]);
        /** @var array{category: string, message_id: string} $sourceMessage */
        foreach ($knownKeysQuery->each() as $sourceMessage) {
            $knownKeys[$sourceMessage['category']][$sourceMessage['message_id']] = true;
        }

        $messageMap = [];
        foreach (self::CATEGORIES as $category) {
            $messages = require ($i18nPath . '/en/' . $category . '.php');
            foreach (array_keys($messages) as $messageId) {
                if (isset($knownKeys[$category][$messageId]) === false) {
                    $b->insert(self::TABLE_SOURCE, [
                        'category' => $category,
                        'message_id' => $messageId,
                    ]);
                    $messageMap[$category][$messageId] = (int) $b->getDb()->getLastInsertID();
                }
            }
        }

        $translationRows = [];
        foreach (self::LOCALES as $locale) {
            foreach (self::CATEGORIES as $category) {
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
        if (empty($translationRows) === false) {
            $b->insertBatch(self::TABLE_MESSAGE, $translationRows, [
                'id',
                'locale',
                'translation',
            ]);
        }
    }
}
