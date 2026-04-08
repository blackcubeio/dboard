<?php

declare(strict_types=1);

/**
 * _results.php
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
use Blackcube\Dboard\Widgets\Widgets;
use Yiisoft\Html\Html;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var SearchForm $searchForm
 * @var array{contents: ?ActiveQueryPaginator, tags: ?ActiveQueryPaginator, menus: ?ActiveQueryPaginator, slugs: ?ActiveQueryPaginator} $paginators
 * @var bool $canViewContents
 * @var bool $canViewTags
 * @var bool $canViewMenus
 * @var bool $canViewSlugs
 * @var CurrentRoute $currentRoute
 */

$search = $searchForm->getSearch();

// Get items from paginators
$contents = $paginators['contents'] !== null ? iterator_to_array($paginators['contents']->read()) : [];
$tags = $paginators['tags'] !== null ? iterator_to_array($paginators['tags']->read()) : [];
$menus = $paginators['menus'] !== null ? iterator_to_array($paginators['menus']->read()) : [];
$slugs = $paginators['slugs'] !== null ? iterator_to_array($paginators['slugs']->read()) : [];

$hasResults = !empty($contents) || !empty($tags) || !empty($menus) || !empty($slugs);

if ($search === '') {
    echo Bleet::emptyState()
        ->icon('magnifying-glass')
        ->title($translator->translate('Search', category: 'dboard-common'))
        ->description($translator->translate('Enter a search term to start.', category: 'dboard-common'))
        ->primary();
} elseif (!$hasResults) {
    echo Bleet::emptyState()
        ->icon('magnifying-glass')
        ->title($translator->translate('No results', category: 'dboard-common'))
        ->description($translator->translate('No results found for "{search}".', ['search' => Html::encode($search)], 'dboard-common'))
        ->primary();
} else {
    // Contents
    if (!empty($contents)) {
        echo Bleet::hr($translator->translate('Contents ({count})', ['count' => $paginators['contents']->getTotalCount()], 'dboard-common'))->info();

        $dl = Bleet::dl()->cols(3);
        foreach ($contents as $content) {
            /** @var Content $content */
            $editUrl = $urlGenerator->generate('dboard.contents.edit', ['id' => $content->getId()]);

            $contentLink = Bleet::a($content->getName() . ' (#' . $content->getId() . ')', $editUrl)->render();

            // URL
            $slug = $content->getSlugId() !== null ? $content->getSlugQuery()->one() : null;
            if ($slug !== null) {
                $host = $slug->getHostQuery()->one();
                $hostName = $host !== null ? $host->getName() : 'localhost';
                $urlLabel = 'https://' . $hostName . '/' . $slug->getPath();
            } else {
                $urlLabel = $translator->translate('Not routable', category: 'dboard-common');
            }

            $urlIcon = Bleet::svg()->outline('link')->addClass('size-4', 'text-secondary-400')->render();
            $urlText = Html::tag('span', Html::encode($urlLabel), ['class' => ['text-sm', 'text-secondary-600']])
                ->encode(false)
                ->render();

            $infoLine = Html::tag('div', $urlIcon . $urlText, ['class' => ['flex', 'items-center', 'gap-1', 'mt-1']])
                ->encode(false)
                ->render();

            $contentDetail = $contentLink . $infoLine;

            $statusBadge = $content->isActive()
                ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
                : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

            $buttonsBar = Bleet::buttonsBar()
                ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->attribute('bleet-popover-trigger', 'search-popover-edit')->button());

            $dl = $dl
                ->addItem(Bleet::termItem($translator->translate('Content', category: 'dboard-common'))->addDetail(Bleet::detailItem($contentDetail)->encode(false)))
                ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
                ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonsBar->render())->encode(false)));
        }
        echo $dl->primary()->render();
        echo Bleet::input()
            ->hidden()
            ->active($searchForm, 'pageContents')
            ->fieldData(['search-pagination' => 'pageContents'])
            ->render();
        echo Html::div()
            ->attribute('dboard-search-pagination', true)
            ->content(Bleet::pagination($paginators['contents'], $urlGenerator)
                ->pageParam('pageContents')
                ->showInfo()
                ->info()
                ->render())
            ->encode(false)
            ->render();
    }

    // Tags
    if (!empty($tags)) {
        echo Bleet::hr($translator->translate('Tags ({count})', ['count' => $paginators['tags']->getTotalCount()], 'dboard-common'))->success();

        $dl = Bleet::dl()->cols(3);
        foreach ($tags as $tag) {
            /** @var Tag $tag */
            $editUrl = $urlGenerator->generate('dboard.tags.edit', ['id' => $tag->getId()]);

            $tagLink = Bleet::a($tag->getName() . ' (#' . $tag->getId() . ')', $editUrl)->render();

            $levelBadge = $tag->getLevel() === 1
                ? Bleet::badge($translator->translate('Category', category: 'dboard-common'))->info()->render()
                : Bleet::badge('Tag')->success()->render();

            $tagDetail = $tagLink;

            $statusBadge = $tag->isActive()
                ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
                : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

            $buttonsBar = Bleet::buttonsBar()
                ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->attribute('bleet-popover-trigger', 'search-popover-edit')->button());

            $dl = $dl
                ->addItem(Bleet::termItem('Tag')->addDetail(Bleet::detailItem($tagDetail)->encode(false))->addDetail(Bleet::detailItem($levelBadge)->encode(false)))
                ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
                ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonsBar->render())->encode(false)));
        }
        echo $dl->primary()->render();
        echo Bleet::input()
            ->hidden()
            ->active($searchForm, 'pageTags')
            ->fieldData(['search-pagination' => 'pageTags'])
            ->render();
        echo Html::div()
            ->attribute('dboard-search-pagination', true)
            ->content(Bleet::pagination($paginators['tags'], $urlGenerator)
                ->pageParam('pageTags')
                ->showInfo()
                ->success()
                ->render())
            ->encode(false)
            ->render();
    }

    // Menus
    if (!empty($menus)) {
        echo Bleet::hr($translator->translate('Menus ({count})', ['count' => $paginators['menus']->getTotalCount()], 'dboard-common'))->warning();

        $dl = Bleet::dl()->cols(3);
        foreach ($menus as $menu) {
            /** @var Menu $menu */
            $editUrl = $urlGenerator->generate('dboard.menus.edit', ['id' => $menu->getId()]);

            $menuLink = Bleet::a($menu->getName() . ' (#' . $menu->getId() . ')', $editUrl)->render();

            // Route
            $routeIcon = Bleet::svg()->outline('arrow-right')->addClass('size-4', 'text-secondary-400')->render();
            $routeText = Html::tag('span', Html::encode($menu->getRoute() ?? $translator->translate('No route', category: 'dboard-common')), ['class' => ['text-sm', 'text-secondary-600']])
                ->encode(false)
                ->render();

            $infoLine = Html::tag('div', $routeIcon . $routeText, ['class' => ['flex', 'items-center', 'gap-1', 'mt-1']])
                ->encode(false)
                ->render();

            $menuDetail = $menuLink . $infoLine;

            $statusBadge = $menu->isActive()
                ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
                : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

            $buttonsBar = Bleet::buttonsBar()
                ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->attribute('bleet-popover-trigger', 'search-popover-edit')->button());

            $dl = $dl
                ->addItem(Bleet::termItem('Menu')->addDetail(Bleet::detailItem($menuDetail)->encode(false)))
                ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
                ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonsBar->render())->encode(false)));
        }
        echo $dl->primary()->render();

        echo Bleet::input()
            ->hidden()
            ->active($searchForm, 'pageMenus')
            ->fieldData(['search-pagination' => 'pageMenus'])
            ->render();
        echo Html::div()
            ->attribute('dboard-search-pagination', true)
            ->content(Bleet::pagination($paginators['menus'], $urlGenerator)
                ->pageParam('pageMenus')
                ->showInfo()
                ->warning()
                ->render())
            ->encode(false)
            ->render();
    }

    // URLs (Slugs redirections)
    if (!empty($slugs)) {
        echo Bleet::hr($translator->translate('Redirections ({count})', ['count' => $paginators['slugs']->getTotalCount()], 'dboard-common'))->accent();

        $dl = Bleet::dl()->cols(4);
        foreach ($slugs as $slug) {
            /** @var Slug $slug */
            $editUrl = $urlGenerator->generate('dboard.slugs.edit', ['id' => $slug->getId()]);

            // Source URL
            $host = $slug->getHostQuery()->one();
            $hostName = $host !== null ? $host->getName() : 'localhost';
            $sourceUrl = 'https://' . $hostName . '/' . $slug->getPath();
            $sourceLink = Bleet::a($sourceUrl . ' (#' . $slug->getId() . ')', $editUrl)->render();

            // Target URL
            $targetUrl = $slug->getTargetUrl();

            // HTTP Code badge
            $httpCode = $slug->getHttpCode();
            $httpCodeBadge = Bleet::badge((string) $httpCode)->info()->render();

            $statusBadge = $slug->isActive()
                ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
                : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

            $buttonsBar = Bleet::buttonsBar()
                ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->attribute('bleet-popover-trigger', 'search-popover-edit')->button());

            $dl = $dl
                ->addItem(Bleet::termItem($translator->translate('Source', category: 'dboard-common'))->addDetail(Bleet::detailItem($sourceLink)->encode(false)))
                ->addItem(Bleet::termItem($translator->translate('Target', category: 'dboard-common'))->detail($targetUrl))
                ->addItem(Bleet::termItem($translator->translate('Code / Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($httpCodeBadge . ' ' . $statusBadge)->encode(false)))
                ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonsBar->render())->encode(false)));
        }
        echo $dl->primary()->render();
        echo Bleet::input()
            ->hidden()
            ->active($searchForm, 'pageSlugs')
            ->fieldData(['search-pagination' => 'pageSlugs'])
            ->render();
        echo Html::div()
            ->attribute('dboard-search-pagination', true)
            ->content(Bleet::pagination($paginators['slugs'], $urlGenerator)
                ->pageParam('pageSlugs')
                ->showInfo()
                ->accent()
                ->render())
            ->encode(false)
            ->render();
    }
<?php echo Widgets::popover($translator->translate('Edit', category: 'dboard-common'))->id('search-popover-edit'); ?>
}
?>
