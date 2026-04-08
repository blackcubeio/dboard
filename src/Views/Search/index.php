<?php

declare(strict_types=1);

/**
 * index.php
 *
 * @copyright 2010-2026 Blackcube
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

<main class="flex-1 p-4 sm:p-6 lg:p-8">

    <?php echo Html::form()
            ->post($searchUrl)
            ->csrf($csrf)
            ->open(); ?>
    <div class="bg-white rounded-lg shadow-sm border border-secondary-200 p-4 mb-6">
            <div class="flex flex-wrap gap-4 items-center">
                <div class="flex-1">
                    <?php echo Bleet::input()
                        ->type('search')
                        ->active($searchForm, 'search')
                        ->placeholder($translator->translate('Search...', category: 'dboard-common'))
                        ->render();
                    ?>
                </div>
                <div>
                    <?php echo Bleet::button($translator->translate('Search', category: 'dboard-common'))
                        ->icon('magnifying-glass')
                        ->submit()
                        ->primary()
                        ->render();
                    ?>
                </div>
            </div>

            <?php if ($canViewContents || $canViewTags || $canViewMenus || $canViewSlugs): ?>
            <div class="flex flex-wrap gap-4 items-center border-t border-secondary-200 pt-4 mt-4">
                <span class="text-sm text-secondary-600"><?php echo $translator->translate('Filter by:', category: 'dboard-common'); ?></span>
                <?php if ($canViewContents): ?>
                    <?php echo Bleet::toggle()
                        ->active($searchForm, 'filterContents')
                        ->label($translator->translate('Contents', category: 'dboard-common'))
                        ->primary()
                        ->render();
                    ?>
                <?php endif; ?>
                <?php if ($canViewTags): ?>
                    <?php echo Bleet::toggle()
                        ->active($searchForm, 'filterTags')
                        ->label('Tags')
                        ->primary()
                        ->render();
                    ?>
                <?php endif; ?>
                <?php if ($canViewMenus): ?>
                    <?php echo Bleet::toggle()
                        ->active($searchForm, 'filterMenus')
                        ->label('Menus')
                        ->primary()
                        ->render();
                    ?>
                <?php endif; ?>
                <?php if ($canViewSlugs): ?>
                    <?php echo Bleet::toggle()
                        ->active($searchForm, 'filterSlugs')
                        ->label('URLs')
                        ->primary()
                        ->render();
                    ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
    </div>

    <?php echo Bleet::cardHeader()
        ->icon('magnifying-glass')
        ->title($translator->translate('Search', category: 'dboard-common'))
        ->primary();
    ?>

    <div class="bg-white rounded-b-lg shadow-lg p-4">
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
