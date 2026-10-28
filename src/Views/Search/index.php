<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Menu;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Models\Forms\SearchForm;
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
 * @var string|null $csrf
 * @var SearchForm $searchForm
 * @var array{contents: ?ActiveQueryPaginator, tags: ?ActiveQueryPaginator, menus: ?ActiveQueryPaginator, slugs: ?ActiveQueryPaginator} $paginators
 * @var bool $canViewContents
 * @var bool $canViewTags
 * @var bool $canViewMenus
 * @var bool $canViewSlugs
 * @var CurrentRoute $currentRoute
 */

$searchUrl = $urlGenerator->generate('dboard.search');
?>

<main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
    <?php echo Widgets::pageHeading($translator->translate('Search', category: 'dboard-common'))->render(); ?>

    <?php echo Html::form()
            ->post($searchUrl)
            ->csrf($csrf)
            ->open(); ?>
    <div class="mb-6 flex flex-col gap-4">
            <div class="flex gap-3">
                <div class="flex-1">
                    <?php echo Bleet::input($searchForm, 'search')
                        ->hideLabel()
                        ->type('search')
                        ->placeholder($translator->translate('Search...', category: 'dboard-common'))
                        ->iconLeft('magnifying-glass')
                        ->render();
                    ?>
                </div>
                <div>
                    <?php echo Bleet::submitButton($translator->translate('Search', category: 'dboard-common'))
                        ->icon('magnifying-glass')
                        ->accent()
                        ->render();
                    ?>
                </div>
            </div>

            <?php if ($canViewContents === true || $canViewTags === true || $canViewMenus === true || $canViewSlugs === true): ?>
            <div class="flex flex-wrap items-center gap-4">
                <span class="text-sm text-secondary-600 dark:text-secondary-300"><?php echo $translator->translate('Filter by:', category: 'dboard-common'); ?></span>
                <?php if ($canViewContents === true): ?>
                    <?php echo Bleet::toggle($searchForm, 'filterContents')
                        ->label($translator->translate('Contents', category: 'dboard-common'))
                        ->secondary()
                        ->render();
                    ?>
                <?php endif; ?>
                <?php if ($canViewTags === true): ?>
                    <?php echo Bleet::toggle($searchForm, 'filterTags')
                        ->label('Tags')
                        ->secondary()
                        ->render();
                    ?>
                <?php endif; ?>
                <?php if ($canViewMenus === true): ?>
                    <?php echo Bleet::toggle($searchForm, 'filterMenus')
                        ->label('Menus')
                        ->secondary()
                        ->render();
                    ?>
                <?php endif; ?>
                <?php if ($canViewSlugs === true): ?>
                    <?php echo Bleet::toggle($searchForm, 'filterSlugs')
                        ->label('URLs')
                        ->secondary()
                        ->render();
                    ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
    </div>

    <div class="dboard-surface p-4">
        <bleet-ajaxify id="search-results">
        <?php
            echo $this->render('Search/_results', [
                'urlGenerator' => $urlGenerator,
                'searchForm' => $searchForm,
                'paginators' => $paginators,
                'canViewContents' => $canViewContents,
                'canViewTags' => $canViewTags,
                'canViewMenus' => $canViewMenus,
                'canViewSlugs' => $canViewSlugs,
                'currentRoute' => $currentRoute,
            ]);
        ?>
        </bleet-ajaxify>
    </div>
    <?php echo Html::closeTag('form'); ?>
</main>
