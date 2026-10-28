<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Tests\Integration;

use Blackcube\Dboard\Migrations\M000000000008CompleteTranslations;
use Blackcube\Dboard\Tests\Support\DatabaseCestTrait;
use Blackcube\Dboard\Tests\Support\IntegrationTester;
use Yiisoft\Db\Migration\Informer\NullMigrationInformer;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Query\Query;

/**
 * The completion of the translation tables against the database: a key the
 * base lost comes back with its six translations, and nothing is added twice.
 * The i18n tables belong to the translator and have no model: they are read
 * and written through the migration builder and the query builder, by the
 * names the migration declares.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class TranslationsCest
{
    use DatabaseCestTrait;

    private const CATEGORY = 'dboard-modules';
    private const MESSAGE = 'Scheduled contents';

    public function testTheCompletionAddsTheMissingKeysOnce(IntegrationTester $I): void
    {
        $I->wantTo('verify the completion migration adds a missing key with its translations, and adds nothing twice');

        $builder = new MigrationBuilder($this->db, new NullMigrationInformer());
        $builder->delete(M000000000008CompleteTranslations::TABLE_SOURCE, [
            'category' => self::CATEGORY,
            'message_id' => self::MESSAGE,
        ]);
        $I->assertSame(0, $this->countSources(), 'the key is gone, its translations with it');

        $migration = new M000000000008CompleteTranslations();
        $migration->up($builder);

        $I->assertSame(1, $this->countSources(), 'the key is back');
        $I->assertSame(6, $this->countMessages(), 'with its six translations');

        $migration->up($builder);

        $I->assertSame(1, $this->countSources(), 'nothing is added twice');
        $I->assertSame(6, $this->countMessages());
    }

    private function querySources(): Query
    {
        return (new Query($this->db))
            ->from(M000000000008CompleteTranslations::TABLE_SOURCE)
            ->andWhere([
                'category' => self::CATEGORY,
                'message_id' => self::MESSAGE,
            ]);
    }

    private function countSources(): int
    {
        return (int) $this->querySources()->count();
    }

    private function countMessages(): int
    {
        $sourceIdsQuery = $this->querySources()->select('id');
        return (int) (new Query($this->db))
            ->from(M000000000008CompleteTranslations::TABLE_MESSAGE)
            ->andWhere([
                'in',
                'id',
                $sourceIdsQuery,
            ])
            ->count();
    }
}
