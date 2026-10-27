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
use Blackcube\Dboard\Widgets\Widgets;
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

$slugSitemapPopover = Bleet::popover($translator->translate('Slug / Sitemap', category: 'dboard-common'))->id('create-popover-slug-sitemap');
$seoPopover = Bleet::popover($translator->translate('*EO', category: 'dboard-common'))->id('create-popover-seo');
$elasticPopover = Bleet::popover($translator->translate('Properties', category: 'dboard-common'))->id('create-popover-elastic');
$tagsPopover = Bleet::popover($translator->translate('Tags', category: 'dboard-common'))->id('create-popover-tags');

$slugSitemapButton = Bleet::button()
    ->icon('link')
    ->outline()
    ->secondary()
    ->xs()
    ->addAttributes($slugSitemapPopover->trigger())
    ->disabled();

$seoButton = Bleet::button()
    ->icon('magnifying-glass')
    ->outline()
    ->info()
    ->xs()
    ->addAttributes($seoPopover->trigger())
    ->disabled();

$elasticButton = Bleet::button()
    ->icon('document-magnifying-glass')
    ->outline()
    ->primary()
    ->xs()
    ->addAttributes($elasticPopover->trigger())
    ->disabled();

$tagsButton = Bleet::button()
    ->icon('tag')
    ->outline()
    ->secondary()
    ->xs()
    ->addAttributes($tagsPopover->trigger())
    ->disabled();

$buttonGroup = Bleet::buttonGroup()
    ->addButton($slugSitemapButton)
    ->addButton($seoButton)
    ->addButton($elasticButton)
    ->addButton($tagsButton);

?>
        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <?php echo Widgets::pageHeading($translator->translate('New content', category: 'dboard-content'))
                ->backTo($urlGenerator->generate('dboard.contents'), $translator->translate('Contents', category: 'dboard-content'))
                ->addAction($buttonGroup->render())
                ->render();
            ?>
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.contents.create'))
                ->csrf($csrf)
                ->noValidate()
                ->open(); ?>

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

                    <div class="mt-6 flex justify-end gap-3">
                        <?php echo Bleet::a($translator->translate('Cancel', category: 'dboard-common'))
                            ->url($urlGenerator->generate('dboard.contents'))
                            ->icon('x-mark')
                            ->ghost()
                            ->secondary()
                            ->render();
                        ?>
                        <?php echo Bleet::submitButton($translator->translate('Save', category: 'dboard-common'))
                            ->icon('check')
                            ->accent()
                            ->render();
                        ?>
                    </div>
            <?php echo $slugSitemapPopover; ?>
            <?php echo $seoPopover; ?>
            <?php echo $elasticPopover; ?>
            <?php echo $tagsPopover; ?>
            <?php echo Html::closeTag('form'); ?>
        </main>
