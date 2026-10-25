<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Content;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\ContentForm;
use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var Administrator $administrator
 * @var ContentForm $formModel
 * @var CurrentRoute $currentRoute
 * @var Content $content
 * @var string|null $csrf
 * @var \Yiisoft\ActiveRecord\ActiveQueryInterface $languageQuery
 * @var \Yiisoft\ActiveRecord\ActiveQueryInterface $typeQuery
 * @var array<int, string> $elasticSchemaOptions
 */

$barButtonClasses = [
    '!rounded-none',
    '!shadow-none',
    'bg-white',
];

$slugSitemapPopover = Bleet::popover($translator->translate('Slug / Sitemap', category: 'dboard-common'))->id('create-popover-slug-sitemap');
$seoPopover = Bleet::popover($translator->translate('*EO', category: 'dboard-common'))->id('create-popover-seo');
$elasticPopover = Bleet::popover($translator->translate('Properties', category: 'dboard-common'))->id('create-popover-elastic');
$tagsPopover = Bleet::popover($translator->translate('Tags', category: 'dboard-common'))->id('create-popover-tags');

$slugSitemapButton = Bleet::button()
    ->icon('link')
    ->outline()
    ->secondary()
    ->xs()
    ->addClass(...$barButtonClasses)
    ->addAttributes($slugSitemapPopover->trigger())
    ->disabled();

$seoButton = Bleet::button()
    ->icon('magnifying-glass')
    ->outline()
    ->info()
    ->xs()
    ->addClass(...$barButtonClasses)
    ->addAttributes($seoPopover->trigger())
    ->disabled();

$elasticButton = Bleet::button()
    ->icon('document-magnifying-glass')
    ->outline()
    ->primary()
    ->xs()
    ->addClass(...$barButtonClasses)
    ->addAttributes($elasticPopover->trigger())
    ->disabled();

$tagsButton = Bleet::button()
    ->icon('tag')
    ->outline()
    ->secondary()
    ->xs()
    ->addClass(...$barButtonClasses)
    ->addAttributes($tagsPopover->trigger())
    ->disabled();

$buttonGroup = Bleet::buttonGroup()
    ->addButton($slugSitemapButton)
    ->addButton($seoButton)
    ->addButton($elasticButton)
    ->addButton($tagsButton);

?>
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.contents.create'))
                ->csrf($csrf)
                ->noValidate()
                ->open(); ?>
                <?php echo Bleet::cardHeader()
                    ->left(Bleet::a()->url($urlGenerator->generate('dboard.contents'))->icon('chevron-left')->outline()->addClass('text-white', 'hover:text-white', '[&>svg]:size-6'))
                    ->title($translator->translate('New content', category: 'dboard-content'))
                    ->button($buttonGroup)
                    ->primary();
                ?>

                <?php echo $this->render('Contents/_form', [
                    'urlGenerator' => $urlGenerator,
                    'administrator' => $administrator,
                    'formModel' => $formModel,
                    'currentRoute' => $currentRoute,
                    'content' => $content,
                    'languageQuery' => $languageQuery,
                    'typeQuery' => $typeQuery,
                    'elasticSchemaOptions' => $elasticSchemaOptions,
                    'isEdit' => false,
                ]); ?>

                    <div class="flex justify-end gap-4 mt-6">
                        <?php echo Bleet::a($translator->translate('Cancel', category: 'dboard-common'))
                            ->url($urlGenerator->generate('dboard.contents'))
                            ->icon('x-mark')
                            ->ghost()
                            ->secondary()
                            ->render();
                        ?>
                        <?php echo Bleet::submitButton($translator->translate('Save', category: 'dboard-common'))
                            ->icon('check')
                            ->primary()
                            ->render();
                        ?>
                    </div>
            <?php echo $slugSitemapPopover; ?>
            <?php echo $seoPopover; ?>
            <?php echo $elasticPopover; ?>
            <?php echo $tagsPopover; ?>
            <?php echo Html::closeTag('form'); ?>
        </main>
