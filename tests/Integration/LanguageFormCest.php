<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Tests\Integration;

use Blackcube\Dboard\Models\Forms\LanguageForm;
use Blackcube\Dboard\Tests\Support\DatabaseCestTrait;
use Blackcube\Dboard\Tests\Support\IntegrationTester;
use Blackcube\Dcore\Models\Language;

/**
 * The language screens against the database: what the form accepts, and
 * what reaches the language once the form is bridged to it.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class LanguageFormCest
{
    use DatabaseCestTrait;

    public function testCreateBridgesTheFormToTheLanguage(IntegrationTester $I): void
    {
        $I->wantTo('verify a posted language form creates the language with its flags');

        $form = new LanguageForm();
        $form->setScenario('create');
        $form->load([
            'LanguageForm' => [
                'id' => 'eo',
                'name' => 'Esperanto',
                'active' => '1',
                'main' => '1',
            ],
        ]);

        $I->assertTrue($form->validate());

        $language = new Language();
        $form->populateModel($language);
        $language->save();

        $saved = Language::query()->andWhere([
            'id' => 'eo',
        ])->one();
        $I->assertInstanceOf(Language::class, $saved);
        $I->assertSame('Esperanto', $saved->getName());
        $I->assertTrue($saved->isMain());
        $I->assertTrue($saved->isActive());
    }

    public function testUncheckedBoxesComeBackFalse(IntegrationTester $I): void
    {
        $I->wantTo('verify a language form posted without its boxes resets them to false');

        $form = new LanguageForm();
        $form->setScenario('edit');
        $form->setActive(true);
        $form->setMain(true);
        $form->load([
            'LanguageForm' => [
                'id' => 'fr-ca',
                'name' => 'Français du Canada',
            ],
        ]);

        $I->assertTrue($form->validate());
        $I->assertFalse($form->isActive());
        $I->assertFalse($form->isMain());
    }

    public function testValidationRefusesAnIdTooLongOrMissing(IntegrationTester $I): void
    {
        $I->wantTo('verify the language form refuses an identifier over eight characters or missing');

        $tooLong = new LanguageForm();
        $tooLong->setScenario('create');
        $tooLong->load([
            'LanguageForm' => [
                'id' => 'fr-canada',
                'name' => 'Français du Canada',
            ],
        ]);

        $I->assertFalse($tooLong->validate());
        $I->assertFalse($tooLong->getValidationResult()->isPropertyValid('id'));
        $I->assertTrue($tooLong->getValidationResult()->isPropertyValid('name'));

        $missing = new LanguageForm();
        $missing->setScenario('create');
        $missing->load([
            'LanguageForm' => [
                'id' => '',
                'name' => '',
            ],
        ]);

        $I->assertFalse($missing->validate());
        $I->assertFalse($missing->getValidationResult()->isPropertyValid('id'));
        $I->assertFalse($missing->getValidationResult()->isPropertyValid('name'));
    }
}
