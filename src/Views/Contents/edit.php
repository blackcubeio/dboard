<?php

declare(strict_types=1);

/**
 * edit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\BlocForm;
use Blackcube\Dboard\Models\Forms\ContentForm;
use Blackcube\Dboard\Components\Rbac;
use Blackcube\Bleet\Aurelia;
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
 * @var ContentForm $formModel
 * @var CurrentRoute $currentRoute
 * @var Content $content
 * @var string|null $csrf
 * @var \Yiisoft\ActiveRecord\ActiveQueryInterface $languageQuery
 * @var \Yiisoft\ActiveRecord\ActiveQueryInterface $typeQuery
 * @var int $languageCount
 * @var BlocForm[] $blocForms
 * @var ElasticSchema[] $allowedElasticSchemas
 * @var array<int, string> $elasticSchemaOptions
 */



// Build toolbar with drawer buttons (always visible, disabled if condition not met)
$hasType = $content->getTypeId() !== null;
$hasSlug = $content->getSlugId() !== null;
$hasElasticSchema = $content->elasticSchemaId !== null;

// Common classes for buttons in ButtonsBar (remove individual rounding/shadow, add white bg)
$barButtonClasses = ['!rounded-none', '!shadow-none', 'bg-white'];

// Slug/Sitemap button
$slugSitemapButton = Bleet::button()
    ->icon('link')
    ->outline()
    ->secondary()
    ->xs()
    ->addClass(...$barButtonClasses)
    ->attribute('bleet-popover-trigger', 'content-popover-slug-sitemap');
if ($hasType) {
    $slugSitemapUrl = $urlGenerator->generate('dboard.contents.slug-sitemap', ['id' => $content->getId()]);
    $slugSitemapButton = $slugSitemapButton->addAttributes(Bleet::drawer()->trigger($slugSitemapUrl));
} else {
    $slugSitemapButton = $slugSitemapButton->disabled();
}

// XEO button
$seoButton = Bleet::button()
    ->icon('magnifying-glass')
    ->outline()
    ->info()
    ->xs()
    ->addClass(...$barButtonClasses)
    ->attribute('bleet-popover-trigger', 'content-popover-seo');
if ($hasSlug) {
    $seoUrl = $urlGenerator->generate('dboard.contents.xeo', ['id' => $content->getId()]);
    $seoButton = $seoButton->addAttributes(Bleet::drawer()->trigger($seoUrl));
} else {
    $seoButton = $seoButton->disabled();
}

// Elastic button
$elasticButton = Bleet::button()
    ->icon('document-magnifying-glass')
    ->outline()
    ->primary()
    ->xs()
    ->addClass(...$barButtonClasses)
    ->attribute('bleet-popover-trigger', 'content-popover-elastic');
if ($hasElasticSchema) {
    $elasticUrl = $urlGenerator->generate('dboard.contents.elastic', ['id' => $content->getId()]);
    $elasticButton = $elasticButton->addAttributes(Bleet::drawer()->trigger($elasticUrl));
} else {
    $elasticButton = $elasticButton->disabled();
}

// Tags button (always enabled)
$tagsUrl = $urlGenerator->generate('dboard.contents.tags', ['id' => $content->getId()]);
$tagsButton = Bleet::button()
    ->icon('tag')
    ->outline()
    ->secondary()
    ->xs()
    ->addClass(...$barButtonClasses)
    ->attribute('bleet-popover-trigger', 'content-popover-tags')
    ->addAttributes(Bleet::drawer()->trigger($tagsUrl));

// Translations button (only enabled if more than 1 language)
$translationsButton = Bleet::button()
    ->icon('language')
    ->outline()
    ->info()
    ->xs()
    ->addClass(...$barButtonClasses)
    ->attribute('bleet-popover-trigger', 'content-popover-translations');
if ($languageCount > 1) {
    $translationsUrl = $urlGenerator->generate('dboard.contents.translations', ['id' => $content->getId()]);
    $translationsButton = $translationsButton->addAttributes(Bleet::drawer()->trigger($translationsUrl));
} else {
    $translationsButton = $translationsButton->disabled();
}

// MdExport button (enabled if has type + IA permission)
$mdExportButton = Bleet::button()
    ->icon('sparkles')
    ->outline()
    ->accent()
    ->xs()
    ->addClass(...$barButtonClasses)
    ->attribute('bleet-popover-trigger', 'content-popover-md-export');
if ($hasType && ($userCan(Rbac::PERMISSION_IA_IMPORT) || $userCan(Rbac::PERMISSION_IA_EXPORT))) {
    $mdExportUrl = $urlGenerator->generate('dboard.contents.md-export', ['id' => $content->getId()]);
    $mdExportButton = $mdExportButton->addAttributes(Bleet::drawer()->trigger($mdExportUrl));
} else {
    $mdExportButton = $mdExportButton->disabled();
}

// Public URL button (enabled if slug exists)
$publicUrlButton = Bleet::button()
    ->icon('globe-alt')
    ->outline()
    ->success()
    ->xs()
    ->addClass(...$barButtonClasses)
    ->attribute('bleet-popover-trigger', 'content-popover-public-url')
    ->disabled();
if ($hasSlug) {
    $slug = $content->getSlugQuery()->one();
    if ($slug !== null) {
        $host = $slug->getHostQuery()->one();
        $hostName = $host !== null ? $host->getName() : null;
        if ($hostName !== null) {
            $publicUrl = $hostName === '*'
                ? '/' . $slug->getPath()
                : 'https://' . $hostName . '/' . $slug->getPath();
            $publicUrlButton = Bleet::a()
                ->url($publicUrl)
                ->icon('globe-alt')
                ->outline()
                ->success()
                ->xs()
                ->addClass(...$barButtonClasses)
                ->attribute('target', '_blank')
                ->attribute('bleet-popover-trigger', 'content-popover-public-url')
                ->button();
        }
    }
}

$buttonsBar = Bleet::buttonsBar()
    ->addButton($slugSitemapButton)
    ->addButton($seoButton)
    ->addButton($elasticButton)
    ->addButton($tagsButton)
    ->addButton($translationsButton)
    ->addButton($mdExportButton)
    ->addButton($publicUrlButton)
?>
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.contents.edit', ['id' => $content->getId()]))
                ->csrf($csrf)
                ->noValidate()
                ->open(); ?>
                <?php
                $cardHeader = Bleet::cardHeader()
                    ->left(Bleet::a()->url($urlGenerator->generate('dboard.contents'))->icon('chevron-left')->outline()->addClass('text-white', 'hover:text-white', '[&>svg]:size-6'))
                    ->title($translator->translate('Content', category: 'dboard-content'))
                    ->button($buttonsBar);
                echo $cardHeader->primary();
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
                    'isEdit' => true,
                ]); ?>

            <?php if (!empty($blocForms) || !empty($allowedElasticSchemas)): ?>
                <?php
                $dndToggleButton = Bleet::button()
                    ->icon('arrows-up-down')
                    ->inverse()
                    ->xs()
                    ->attribute('dboard-drag-drop-trigger', Aurelia::attributesCustomAttribute([
                        'id' => 'content-blocs-list',
                    ]));
                ?>
                <?php echo Bleet::cardHeader()
                    ->title($translator->translate('Blocks', category: 'dboard-common'))
                    ->button($dndToggleButton)
                    ->addClass('mt-6')
                    ->secondary();
                ?>
                <div class="bg-white rounded-b-lg shadow-lg p-6">
                    <bleet-ajaxify id="content-blocs-list">
                    <?php echo $this->render('Commons/_blocs', [
                        'urlGenerator' => $urlGenerator,
                        'model' => $content,
                        'blocForms' => $blocForms,
                        'allowedElasticSchemas' => $allowedElasticSchemas,
                        'adminTemplatesPath' => $adminTemplatesPath ?? null,
                        'dndMode' => $dndMode ?? false,
                        'reorderRoute' => $reorderRoute,
                        'addRoute' => $addRoute,
                        'deleteRoute' => $deleteRoute,
                        'fileEndpoints' => $fileEndpoints,
                        'blocsListId' => $blocsListId,
                        'routeIdParam' => $routeIdParam,
                    ]); ?>
                    </bleet-ajaxify>
                </div>
            <?php endif; ?>

                <div class="flex justify-end gap-4 mt-6">
                    <?php echo Bleet::a($translator->translate('Cancel', category: 'dboard-common'))
                        ->url($urlGenerator->generate('dboard.contents'))
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

            <?php echo Html::closeTag('form'); ?>

            <?php // Popover tooltips for button bar ?>
            <?php echo Widgets::popover($translator->translate('Slug / Sitemap', category: 'dboard-common'))->id('content-popover-slug-sitemap'); ?>
            <?php echo Widgets::popover($translator->translate('*EO', category: 'dboard-common'))->id('content-popover-seo'); ?>
            <?php echo Widgets::popover($translator->translate('Properties', category: 'dboard-common'))->id('content-popover-elastic'); ?>
            <?php echo Widgets::popover($translator->translate('Tags', category: 'dboard-common'))->id('content-popover-tags'); ?>
            <?php echo Widgets::popover($translator->translate('Translations', category: 'dboard-common'))->id('content-popover-translations'); ?>
            <?php echo Widgets::popover($translator->translate('AI Export', category: 'dboard-common'))->id('content-popover-md-export'); ?>
            <?php echo Widgets::popover($translator->translate('View on site', category: 'dboard-common'))->id('content-popover-public-url'); ?>
        </main>
