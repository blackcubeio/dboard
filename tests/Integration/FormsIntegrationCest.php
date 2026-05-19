<?php

declare(strict_types=1);

/**
 * FormsIntegrationCest.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Tests\Integration;

use Blackcube\Dboard\Models\Forms\BlocForm;
use Blackcube\Dboard\Models\Forms\ContentForm;
use Blackcube\Dboard\Models\Forms\TagForm;
use Blackcube\Dboard\Tests\Support\DatabaseCestTrait;
use Blackcube\Dboard\Tests\Support\IntegrationTester;
use Blackcube\Dcore\Models\Bloc;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Tag;
use Blackcube\ActiveRecord\Elastic\ElasticSchema;
use DateTimeImmutable;

/**
 * Integration tests for ContentForm, TagForm, BlocForm.
 */
final class FormsIntegrationCest
{
    use DatabaseCestTrait;

    // ========================================
    // ContentForm tests
    // ========================================

    public function testContentFormFromActiveRecordLoadsProps(IntegrationTester $I): void
    {
        $I->wantTo('verify ContentForm::createFromModel loads all properties');

        $content = new Content();
        $content->setName('Test Content');
        $content->setLanguageId('fr');
        $content->setActive(false);
        $content->setDateStart(new DateTimeImmutable('2025-01-01'));
        $content->save();

        $form = ContentForm::createFromModel($content);

        $I->assertEquals($content->getId(), $form->getId());
        $I->assertEquals('Test Content', $form->getName());
        $I->assertEquals('fr', $form->getLanguageId());
        $I->assertFalse($form->isActive());
        $I->assertEquals('2025-01-01', $form->getDateStart());
    }

    public function testContentFormPopulateActiveRecordModifiesAr(IntegrationTester $I): void
    {
        $I->wantTo('verify ContentForm::populateModel modifies AR');

        $content = new Content();
        $content->setName('Original');
        $content->setLanguageId('fr');
        $content->save();

        $form = ContentForm::createFromModel($content);
        $form->setScenario('edit');
        $form->setName('Modified');
        $form->setActive(false);

        $form->populateModel($content);
        $content->save();

        // Reload from DB
        $reloaded = Content::query()->andWhere(['id' => $content->getId()])->one();
        $I->assertEquals('Modified', $reloaded->getName());
        $I->assertFalse($reloaded->isActive());
    }

    public function testContentFormValidationPasses(IntegrationTester $I): void
    {
        $I->wantTo('verify ContentForm validation passes with valid data');

        $content = new Content();
        $content->setName('Valid');
        $content->setLanguageId('fr');
        $content->save();

        $form = ContentForm::createFromModel($content);
        $form->setScenario('edit');

        $I->assertTrue($form->validate());
    }

    public function testContentFormValidationFails(IntegrationTester $I): void
    {
        $I->wantTo('verify ContentForm validation fails with empty name');

        $content = new Content();
        $content->setName('Valid');
        $content->setLanguageId('fr');
        $content->save();

        $form = ContentForm::createFromModel($content);
        $form->setScenario('edit');
        $form->setName(''); // Invalid

        $I->assertFalse($form->validate());
    }

    // ========================================
    // TagForm tests
    // ========================================

    public function testTagFormFromActiveRecordLoadsProps(IntegrationTester $I): void
    {
        $I->wantTo('verify TagForm::createFromModel loads all properties');

        $tag = new Tag();
        $tag->setName('Test Tag');
        $tag->setActive(false);
        $tag->save();

        $form = TagForm::createFromModel($tag);

        $I->assertEquals($tag->getId(), $form->getId());
        $I->assertEquals('Test Tag', $form->getName());
        $I->assertFalse($form->isActive());
    }

    public function testTagFormPopulateActiveRecordModifiesAr(IntegrationTester $I): void
    {
        $I->wantTo('verify TagForm::populateModel modifies AR');

        $tag = new Tag();
        $tag->setName('Original');
        $tag->save();

        $form = TagForm::createFromModel($tag);
        $form->setScenario('edit');
        $form->setName('Modified');
        $form->setActive(false);

        $form->populateModel($tag);
        $tag->save();

        $reloaded = Tag::query()->andWhere(['id' => $tag->getId()])->one();
        $I->assertEquals('Modified', $reloaded->getName());
        $I->assertFalse($reloaded->isActive());
    }

    public function testTagFormValidationPasses(IntegrationTester $I): void
    {
        $I->wantTo('verify TagForm validation passes with valid data');

        $tag = new Tag();
        $tag->setName('Valid Tag');
        $tag->save();

        $form = TagForm::createFromModel($tag);
        $form->setScenario('edit');
        $form->setLanguageId('fr');

        $I->assertTrue($form->validate());
    }

    public function testTagFormValidationFails(IntegrationTester $I): void
    {
        $I->wantTo('verify TagForm validation fails with empty name');

        $tag = new Tag();
        $tag->setName('Valid');
        $tag->save();

        $form = TagForm::createFromModel($tag);
        $form->setScenario('edit');
        $form->setName('');

        $I->assertFalse($form->validate());
    }

    // ========================================
    // BlocForm tests
    // ========================================

    public function testBlocFormFromActiveRecordLoadsProps(IntegrationTester $I): void
    {
        $I->wantTo('verify BlocForm::createFromModel loads all properties');

        $schema = new ElasticSchema();
        $schema->setName('TestSchema');
        $schema->setSchema('{"type":"object","properties":{}}');
        $schema->save();

        $bloc = new Bloc();
        $bloc->setActive(false);
        $bloc->setElasticSchemaId($schema->getId());
        $bloc->save();

        $form = BlocForm::createFromModel($bloc);

        $I->assertEquals($bloc->getId(), $form->getId());
        $I->assertFalse($form->isActive());
    }

    public function testBlocFormPopulateActiveRecordModifiesAr(IntegrationTester $I): void
    {
        $I->wantTo('verify BlocForm::populateModel modifies AR');

        $schema = new ElasticSchema();
        $schema->setName('TestSchema');
        $schema->setSchema('{"type":"object","properties":{}}');
        $schema->save();

        $bloc = new Bloc();
        $bloc->setActive(true);
        $bloc->setElasticSchemaId($schema->getId());
        $bloc->save();

        $form = BlocForm::createFromModel($bloc);
        $form->setScenario('edit');
        $form->setActive(false);

        $form->populateModel($bloc);
        $bloc->save();

        $reloaded = Bloc::query()->andWhere(['id' => $bloc->getId()])->one();
        $I->assertFalse($reloaded->isActive());
    }

    public function testBlocFormValidationPasses(IntegrationTester $I): void
    {
        $I->wantTo('verify BlocForm validation passes');

        $schema = new ElasticSchema();
        $schema->setName('TestSchema');
        $schema->setSchema('{"type":"object","properties":{}}');
        $schema->save();

        $bloc = new Bloc();
        $bloc->setActive(true);
        $bloc->setElasticSchemaId($schema->getId());
        $bloc->save();

        $form = BlocForm::createFromModel($bloc);
        $form->setScenario('edit');

        $I->assertTrue($form->validate());
    }
}
