<?php

declare(strict_types=1);

/**
 * create.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\TagForm;
use Blackcube\Bleet\Bleet;
use Blackcube\Dboard\Widgets\Widgets;
use Yiisoft\Html\Html;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var Administrator $administrator
 * @var TagForm $formModel
 * @var CurrentRoute $currentRoute
 * @var Tag $tag
 * @var string|null $csrf
 * @var array<int, string> $targetOptions
 * @var \Yiisoft\ActiveRecord\ActiveQueryInterface $languageQuery
 * @var \Yiisoft\ActiveRecord\ActiveQueryInterface $typeQuery
 * @var array<int, string> $elasticSchemaOptions
 */

// Toolbar buttons (all disabled in create mode)
$barButtonClasses = ['!rounded-none', '!shadow-none', 'bg-white'];

$slugSitemapButton = Bleet::button()
    ->icon('link')
    ->outline()
    ->secondary()
    ->xs()
    ->addClass(...$barButtonClasses)
    ->attribute('bleet-popover-trigger', 'create-popover-slug-sitemap')
    ->disabled();

$seoButton = Bleet::button()
    ->icon('magnifying-glass')
    ->outline()
    ->info()
    ->xs()
    ->addClass(...$barButtonClasses)
    ->attribute('bleet-popover-trigger', 'create-popover-seo')
    ->disabled();

$elasticButton = Bleet::button()
    ->icon('document-magnifying-glass')
    ->outline()
    ->primary()
    ->xs()
    ->addClass(...$barButtonClasses)
    ->attribute('bleet-popover-trigger', 'create-popover-elastic')
    ->disabled();

$tagsButton = Bleet::button()
    ->icon('tag')
    ->outline()
    ->secondary()
    ->xs()
    ->addClass(...$barButtonClasses)
    ->attribute('bleet-popover-trigger', 'create-popover-tags')
    ->disabled();

$buttonsBar = Bleet::buttonsBar()
    ->addButton($slugSitemapButton)
    ->addButton($seoButton)
    ->addButton($elasticButton)
    ->addButton($tagsButton);

?>
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.tags.create'))
                ->csrf($csrf)
                ->noValidate()
                ->open(); ?>
                <?php echo Bleet::cardHeader()
                    ->left(Bleet::a()->url($urlGenerator->generate('dboard.tags'))->icon('chevron-left')->outline()->addClass('text-white', 'hover:text-white', '[&>svg]:size-6'))
                    ->title($translator->translate('New tag', category: 'dboard-content'))
                    ->button($buttonsBar)
                    ->primary();
                ?>

                <?php echo $this->render('Tags/_form', [
                    'urlGenerator' => $urlGenerator,
                    'administrator' => $administrator,
                    'formModel' => $formModel,
                    'currentRoute' => $currentRoute,
                    'tag' => $tag,
                    'languageQuery' => $languageQuery,
                    'typeQuery' => $typeQuery,
                    'elasticSchemaOptions' => $elasticSchemaOptions,
                    'isEdit' => false,
                ]); ?>

                    <div class="flex justify-end gap-4 mt-6">
                        <?php echo Bleet::a($translator->translate('Cancel', category: 'dboard-common'))
                            ->url($urlGenerator->generate('dboard.tags'))
                            ->icon('x-mark')
                            ->ghost()
                            ->secondary()
                            ->render();
                        ?>
                        <?php echo Bleet::button($translator->translate('Save', category: 'dboard-common'))
                            ->icon('check')
                            ->submit()
                            ->primary()
                            ->render();
                        ?>
                    </div>
            <?php // Popover tooltips for button bar ?>
            <?php echo Widgets::popover($translator->translate('Slug / Sitemap', category: 'dboard-common'))->id('create-popover-slug-sitemap'); ?>
            <?php echo Widgets::popover($translator->translate('*EO', category: 'dboard-common'))->id('create-popover-seo'); ?>
            <?php echo Widgets::popover($translator->translate('Properties', category: 'dboard-common'))->id('create-popover-elastic'); ?>
            <?php echo Widgets::popover($translator->translate('Tags', category: 'dboard-common'))->id('create-popover-tags'); ?>
            <?php echo Html::closeTag('form'); ?>
        </main>
