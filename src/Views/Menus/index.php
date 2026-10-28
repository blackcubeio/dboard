<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\PageForm;
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
 * @var Administrator $administrator
 * @var ActiveQueryPaginator $paginator
 * @var SearchForm $searchForm
 * @var PageForm $pageForm
 * @var CurrentRoute $currentRoute
 * @var string|null $csrf
 */

?>


        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <?php echo Widgets::pageHeading($translator->translate('Menus', category: 'dboard-content'))
                ->addAction(Bleet::a($translator->translate('Create', category: 'dboard-common'), $urlGenerator->generate('dboard.menus.create'))
                    ->button()
                    ->icon('plus')
                    ->accent()
                    ->render())
                ->render();
            ?>

            <?php echo Html::form()->post($urlGenerator->generate('dboard.menus'))->csrf($csrf)->attribute('class', 'mb-4')->open(); ?>
                <?php echo Bleet::input($searchForm, 'search')
                    ->placeholder($translator->translate('Search', category: 'dboard-common'))
                    ->iconLeft('magnifying-glass')
                    ->hideLabel()
                    ->text()
                    ->render();
                ?>
            <?php echo Html::form()->close(); ?>

            <div class="dboard-surface p-4">
                <bleet-ajaxify id="menus-list">
                <?php
                    echo $this->render('Menus/_list', [
                        'urlGenerator' => $urlGenerator,
                        'administrator' => $administrator,
                        'paginator' => $paginator,
                        'searchForm' => $searchForm,
                        'pageForm' => $pageForm,
                        'currentRoute' => $currentRoute,
                        'items' => $items ?? null,
                    ]);
                ?>
                </bleet-ajaxify>
            </div>

            <div class="mt-6">
                <?php echo Widgets::pagination($paginator, $urlGenerator, $translator)
                    ->showInfo()
                    ->primary()
                    ->render();
                ?>
            </div>
        </main>
