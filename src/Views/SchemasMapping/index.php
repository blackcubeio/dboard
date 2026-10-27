<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

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
 */

?>

        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <?php echo Widgets::pageHeading($translator->translate('Xeo Mappings', category: 'dboard-modules'))
                ->addAction(Bleet::a($translator->translate('Create', category: 'dboard-common'), $urlGenerator->generate('dboard.xeo.mapping.create'))
                    ->button()
                    ->icon('plus')
                    ->accent()
                    ->render())
                ->render();
            ?>

            <div class="dboard-surface p-4">
                <bleet-ajaxify id="mapping-list">
                <?php
                    echo $this->render('SchemasMapping/_list', [
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
                <?php echo Widgets::pagination($paginator, $urlGenerator, $translator)
                    ->showInfo()
                    ->primary()
                    ->render();
                ?>
            </div>
        </main>
