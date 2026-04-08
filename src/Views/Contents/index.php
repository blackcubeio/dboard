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
use Blackcube\Dboard\Enums\ListMode;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\PageForm;
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
 * @var Administrator $administrator
 * @var ActiveQueryPaginator $paginator
 * @var SearchForm $searchForm
 * @var PageForm $pageForm
 * @var CurrentRoute $currentRoute
 * @var ListMode $mode
 * @var string|null $csrf
 */

$isFlat = $mode === ListMode::Flat;
$toggleUrl = $urlGenerator->generate('dboard.contents') . '?mode=' . ($isFlat ? 'tree' : 'flat');
$toggleIcon = $isFlat ? 'list-bullet' : 'clock';
$toggleLabel = $isFlat
    ? $translator->translate('Tree view', category: 'dboard-common')
    : $translator->translate('Recent', category: 'dboard-common');

?>


        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <div class="bg-white rounded-lg shadow-sm border border-secondary-200 p-4 mb-6">
                <?php echo Html::form()->post($urlGenerator->generate('dboard.contents'))->csrf($csrf)->attribute('class', 'flex flex-wrap gap-4 items-center')->open(); ?>
                    <div class="flex-1">
                        <?php echo Bleet::input()
                            ->active($searchForm, 'search')
                            ->text()
                            ->render();
                        ?>
                    </div>
                    <div class="flex gap-2">
                        <?php echo Bleet::a($toggleLabel, $toggleUrl)
                            ->button()
                            ->icon($toggleIcon)
                            ->outline()
                            ->secondary()
                            ->render();
                        ?>
                        <?php echo Bleet::a($translator->translate('Create', category: 'dboard-common'), $urlGenerator->generate('dboard.contents.create'))
                            ->button()
                            ->icon('plus')
                            ->primary()
                            ->render();
                        ?>
                    </div>
                <?php echo Html::form()->close(); ?>
            </div>

            <?php echo Bleet::cardHeader()
                ->icon('document-text')
                ->title($translator->translate('Contents', category: 'dboard-content'))
                ->primary();
            ?>

            <div class="bg-white rounded-b-lg shadow-lg p-4">
                <bleet-ajaxify id="contents-list">
                <?php
                    $listView = $isFlat ? 'Contents/_list_flat' : 'Contents/_list';
                    echo $this->render($listView, [
                        'urlGenerator' => $urlGenerator,
                        'administrator' => $administrator,
                        'paginator' => $paginator,
                        'searchForm' => $searchForm,
                        'pageForm' => $pageForm,
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
