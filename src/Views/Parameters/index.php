<?php

declare(strict_types=1);

/**
 * index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Parameter;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Bleet\Bleet;
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
 */

?>


        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <div class="bg-white rounded-lg shadow-sm border border-secondary-200 p-4 mb-6">
                <?php echo Html::form()->get($urlGenerator->generate('dboard.parameters'))->attribute('class', 'flex flex-wrap gap-4 items-center')->open(); ?>
                    <div class="flex-1">
                        <?php echo Bleet::input()
                            ->type('search')
                            ->name('search')
                            ->value($searchForm->getSearch())
                            ->placeholder($translator->translate('Search', category: 'dboard-common'))
                            ->render();
                        ?>
                    </div>
                    <div>
                        <?php echo Bleet::a($translator->translate('Create', category: 'dboard-common'), $urlGenerator->generate('dboard.parameters.create'))
                            ->button()
                            ->icon('plus')
                            ->primary()
                            ->render();
                        ?>
                    </div>
                <?php echo Html::form()->close(); ?>
            </div>

            <?php echo Bleet::cardHeader()
                ->icon('cog-6-tooth')
                ->title($translator->translate('Parameters', category: 'dboard-modules'))
                ->primary();
            ?>

            <div class="bg-white rounded-b-lg shadow-lg p-4">
                <bleet-ajaxify id="parameters-list">
                <?php
                    echo $this->render('Parameters/_list', [
                        'urlGenerator' => $urlGenerator,
                        'administrator' => $administrator,
                        'paginator' => $paginator,
                        'searchForm' => $searchForm,
                        'currentRoute' => $currentRoute,
                    ]);
                ?>
                </bleet-ajaxify>
            </div>

            <div class="mt-6">
                <?php echo Bleet::pagination($paginator, $urlGenerator)
                    ->showInfo()
                    ->primary()
                    ->render();
                ?>
            </div>
        </main>

