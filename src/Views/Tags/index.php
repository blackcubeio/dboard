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
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Enums\ListMode;
use Blackcube\Dboard\Helpers\RouteHelper;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\FilterNodeForm;
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
 * @var FilterNodeForm $filterNodeForm
 * @var CurrentRoute $currentRoute
 * @var ListMode $mode
 * @var int|null $nodeId
 * @var string|null $csrf
 */

$isFlat = $mode === ListMode::Flat;
$nodeOptions = RouteHelper::create()->getTagRoutes(byId: true, query: Tag::query());

?>


        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <div class="bg-white rounded-lg shadow-sm border border-secondary-200 p-4 mb-6">
                <?php echo Html::form()->post($urlGenerator->generate('dboard.tags'))->csrf($csrf)->attribute('class', 'flex flex-wrap gap-4 items-center')->open(); ?>
                    <div class="flex-1">
                        <?php echo Bleet::input()
                            ->active($searchForm, 'search')
                            ->text()
                            ->render();
                        ?>
                    </div>
                    <div class="flex gap-2">
                        <?php echo Bleet::a($translator->translate('Create', category: 'dboard-common'), $urlGenerator->generate('dboard.tags.create'))
                            ->button()
                            ->icon('plus')
                            ->primary()
                            ->render();
                        ?>
                    </div>
                <?php echo Html::form()->close(); ?>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-secondary-200 p-4 mb-6">
                <?php echo Html::form()->post($urlGenerator->generate('dboard.tags'))->csrf($csrf)->attribute('class', 'flex flex-wrap gap-4 items-center justify-end')->open(); ?>
                    <?php echo Bleet::select()
                        ->active($filterNodeForm, 'nodeId')
                        ->placeholder($translator->translate('-- All tags --', category: 'dboard-content'))
                        ->options($nodeOptions)
                        ->wrapperAddClass('w-92')
                        ->searchable()
                        ->secondary()
                        ->render();
                    ?>
                    <?php echo Bleet::toggle()
                        ->active($filterNodeForm, 'modeFlat')
                        ->label($translator->translate('Recent', category: 'dboard-common'))
                        ->secondary()
                        ->render();
                    ?>
                    <?php echo Bleet::button('OK')
                        ->submit()
                        ->secondary()
                        ->render();
                    ?>
                <?php echo Html::form()->close(); ?>
            </div>

            <?php echo Bleet::cardHeader()
                ->icon('document-text')
                ->title($translator->translate('Tags', category: 'dboard-content'))
                ->primary();
            ?>

            <div class="bg-white rounded-b-lg shadow-lg p-4">
                <bleet-ajaxify id="tags-list">
                <?php
                    $listView = $isFlat ? 'Tags/_list_flat' : 'Tags/_list';
                    echo $this->render($listView, [
                        'urlGenerator' => $urlGenerator,
                        'administrator' => $administrator,
                        'paginator' => $paginator,
                        'searchForm' => $searchForm,
                        'pageForm' => $pageForm,
                        'currentRoute' => $currentRoute,
                        'levelOffset' => $levelOffset,
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
