<?php

declare(strict_types=1);

/**
 * index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Html\Html;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Blackcube\Dboard\Models\Forms\SearchForm;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var OffsetPaginator $paginator
 * @var SearchForm $searchForm
 * @var CurrentRoute $currentRoute
 * @var int|null $currentUserId
 */

?>


        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <div class="bg-white rounded-lg shadow-sm border border-secondary-200 p-4 mb-6">
                <?php echo Html::form()->get($urlGenerator->generate('dboard.administrators'))->attribute('class', 'flex flex-wrap gap-4 items-center')->open(); ?>
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
                        <?php echo Bleet::a($translator->translate('Create', category: 'dboard-common'), $urlGenerator->generate('dboard.administrators.create'))
                            ->button()
                            ->icon('plus')
                            ->primary()
                            ->render();
                        ?>
                    </div>
                <?php echo Html::form()->close(); ?>
            </div>

            <?php echo Bleet::cardHeader()
                ->icon('users')
                ->title($translator->translate('Administrators', category: 'dboard-modules'))
                ->primary();
            ?>

            <div class="bg-white rounded-b-lg shadow-lg p-4">
                <bleet-ajaxify id="administrators-list">
                <?php
                    echo $this->render('Administrators/_list', [
                        'urlGenerator' => $urlGenerator,
                        'paginator' => $paginator,
                        'searchForm' => $searchForm,
                        'currentRoute' => $currentRoute,
                        'currentUserId' => $currentUserId,
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

