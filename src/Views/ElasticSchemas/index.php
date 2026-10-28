<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Bleet\Bleet;
use Blackcube\Dboard\Widgets\Widgets;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Html\Html;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var Administrator $administrator
 * @var OffsetPaginator $paginator
 * @var \Blackcube\Dboard\Models\Forms\SearchForm $searchForm
 * @var \Blackcube\Dboard\Models\Forms\PageForm $pageForm
 * @var CurrentRoute $currentRoute
 * @var array<int, true> $usedElasticSchemaIds
 */

?>


        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <?php echo Widgets::pageHeading($translator->translate('Elastic schemas', category: 'dboard-modules'))
                ->addAction(Bleet::a($translator->translate('Create', category: 'dboard-common'), $urlGenerator->generate('dboard.elasticschemas.create'))
                    ->button()
                    ->icon('plus')
                    ->accent()
                    ->render())
                ->render();
            ?>

            <?php echo Html::form()->get($urlGenerator->generate('dboard.elasticschemas'))->attribute('class', 'mb-4')->open(); ?>
                <?php echo Bleet::input()
                    ->type('search')
                    ->name('search')
                    ->value($searchForm->getSearch())
                    ->placeholder($translator->translate('Search', category: 'dboard-common'))
                    ->iconLeft('magnifying-glass')
                    ->hideLabel()
                    ->render();
                ?>
            <?php echo Html::form()->close(); ?>

            <div class="dboard-surface p-4">
                <bleet-ajaxify id="elasticschemas-list">
                <?php
                    echo $this->render('ElasticSchemas/_list', [
                        'urlGenerator' => $urlGenerator,
                        'administrator' => $administrator,
                        'paginator' => $paginator,
                        'searchForm' => $searchForm,
                        'currentRoute' => $currentRoute,
                        'items' => $items ?? null,
                        'usedElasticSchemaIds' => $usedElasticSchemaIds,
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
