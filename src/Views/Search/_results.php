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
 * @var SearchForm $searchForm
 * @var array{contents: ?ActiveQueryPaginator, tags: ?ActiveQueryPaginator, menus: ?ActiveQueryPaginator, slugs: ?ActiveQueryPaginator} $paginators
 * @var bool $canViewContents
 * @var bool $canViewTags
 * @var bool $canViewMenus
 * @var bool $canViewSlugs
 * @var CurrentRoute $currentRoute
 */

$search = $searchForm->getSearch();

$contents = $paginators['contents'] !== null ? iterator_to_array($paginators['contents']->read()) : [];
$tags = $paginators['tags'] !== null ? iterator_to_array($paginators['tags']->read()) : [];
$menus = $paginators['menus'] !== null ? iterator_to_array($paginators['menus']->read()) : [];
$slugs = $paginators['slugs'] !== null ? iterator_to_array($paginators['slugs']->read()) : [];

$hasResults = empty($contents) === false || empty($tags) === false || empty($menus) === false || empty($slugs) === false;

if ($search === ''):
    echo Bleet::emptyState()
        ->icon('magnifying-glass')
        ->title($translator->translate('Search', category: 'dboard-common'))
        ->description($translator->translate('Enter a search term to start.', category: 'dboard-common'))
        ->primary();
elseif ($hasResults === false):
    echo Bleet::emptyState()
        ->icon('magnifying-glass')
        ->title($translator->translate('No results', category: 'dboard-common'))
        ->description($translator->translate('No results found for "{search}".', [
            'search' => Html::encode($search),
        ], 'dboard-common'))
        ->primary();
else:
    $editPopover = Bleet::popover($translator->translate('Edit', category: 'dboard-common'))->id('search-popover-edit');
    if (empty($contents) === false):
        echo Bleet::hr($translator->translate('Contents ({count})', [
            'count' => $paginators['contents']->getTotalItems(),
        ], 'dboard-common'))->info();

        $dl = Bleet::dl()->cols(3);
        foreach ($contents as $content):
            /** @var Content $content */
            $editUrl = $urlGenerator->generate('dboard.contents.edit', [
                'id' => $content->getId(),
            ]);

            $contentLink = Bleet::a($content->getName() . ' (#' . $content->getId() . ')', $editUrl)->render();

            $slug = $content->getSlugId() !== null ? $content->slug : null;
            if ($slug !== null):
                $host = $slug->host;
                $hostName = $host !== null ? $host->getName() : 'localhost';
                $urlLabel = 'https://' . $hostName . '/' . $slug->getPath();
            else:
                $urlLabel = $translator->translate('Not routable', category: 'dboard-common');
            endif;

            $urlIcon = Bleet::svg()->outline('link')->addClass('size-4', 'text-secondary-400')->render();
            $urlText = Html::tag('span', Html::encode($urlLabel), [
                'class' => [
                    'text-sm',
                    'text-secondary-600',
                ],
            ])
                ->encode(false)
                ->render();

            $infoLine = Html::tag('div', $urlIcon . $urlText, [
                'class' => [
                    'flex',
                    'items-center',
                    'gap-1',
                    'mt-1',
                ],
            ])
                ->encode(false)
                ->render();

            $contentDetail = $contentLink . $infoLine;

            $statusBadge = $content->isActive() === true
                ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
                : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

            $buttonGroup = Bleet::buttonGroup()
                ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->addAttributes($editPopover->trigger())->button());

            $dl = $dl
                ->addItem(Bleet::termItem($translator->translate('Content', category: 'dboard-common'))->addDetail(Bleet::detailItem($contentDetail)->encode(false)))
                ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
                ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonGroup->render())->encode(false)));
        endforeach;
        echo $dl->primary()->render();
        echo Bleet::input($searchForm, 'pageContents')
            ->hidden()
            ->fieldData([
                'search-pagination' => 'pageContents',
            ])
            ->render();
        echo Html::div()
            ->attribute('dboard-search-pagination', true)
            ->content(Widgets::pagination($paginators['contents'], $urlGenerator, $translator)
                ->pageParam('pageContents')
                ->showInfo()
                ->info()
                ->render())
            ->encode(false)
            ->render();
    endif;

    if (empty($tags) === false):
        echo Bleet::hr($translator->translate('Tags ({count})', [
            'count' => $paginators['tags']->getTotalItems(),
        ], 'dboard-common'))->success();

        $dl = Bleet::dl()->cols(3);
        foreach ($tags as $tag):
            /** @var Tag $tag */
            $editUrl = $urlGenerator->generate('dboard.tags.edit', [
                'id' => $tag->getId(),
            ]);

            $tagLink = Bleet::a($tag->getName() . ' (#' . $tag->getId() . ')', $editUrl)->render();

            $levelBadge = $tag->getLevel() === 1
                ? Bleet::badge($translator->translate('Category', category: 'dboard-common'))->info()->render()
                : Bleet::badge('Tag')->success()->render();

            $tagDetail = $tagLink;

            $statusBadge = $tag->isActive() === true
                ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
                : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

            $buttonGroup = Bleet::buttonGroup()
                ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->addAttributes($editPopover->trigger())->button());

            $dl = $dl
                ->addItem(Bleet::termItem('Tag')->addDetail(Bleet::detailItem($tagDetail)->encode(false))->addDetail(Bleet::detailItem($levelBadge)->encode(false)))
                ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
                ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonGroup->render())->encode(false)));
        endforeach;
        echo $dl->primary()->render();
        echo Bleet::input($searchForm, 'pageTags')
            ->hidden()
            ->fieldData([
                'search-pagination' => 'pageTags',
            ])
            ->render();
        echo Html::div()
            ->attribute('dboard-search-pagination', true)
            ->content(Widgets::pagination($paginators['tags'], $urlGenerator, $translator)
                ->pageParam('pageTags')
                ->showInfo()
                ->success()
                ->render())
            ->encode(false)
            ->render();
    endif;

    if (empty($menus) === false):
        echo Bleet::hr($translator->translate('Menus ({count})', [
            'count' => $paginators['menus']->getTotalItems(),
        ], 'dboard-common'))->warning();

        $dl = Bleet::dl()->cols(3);
        foreach ($menus as $menu):
            /** @var Menu $menu */
            $editUrl = $urlGenerator->generate('dboard.menus.edit', [
                'id' => $menu->getId(),
            ]);

            $menuLink = Bleet::a($menu->getName() . ' (#' . $menu->getId() . ')', $editUrl)->render();

            $routeIcon = Bleet::svg()->outline('arrow-right')->addClass('size-4', 'text-secondary-400')->render();
            $routeText = Html::tag('span', Html::encode($menu->getRoute() ?? $translator->translate('No route', category: 'dboard-common')), [
                'class' => [
                    'text-sm',
                    'text-secondary-600',
                ],
            ])
                ->encode(false)
                ->render();

            $infoLine = Html::tag('div', $routeIcon . $routeText, [
                'class' => [
                    'flex',
                    'items-center',
                    'gap-1',
                    'mt-1',
                ],
            ])
                ->encode(false)
                ->render();

            $menuDetail = $menuLink . $infoLine;

            $statusBadge = $menu->isActive() === true
                ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
                : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

            $buttonGroup = Bleet::buttonGroup()
                ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->addAttributes($editPopover->trigger())->button());

            $dl = $dl
                ->addItem(Bleet::termItem('Menu')->addDetail(Bleet::detailItem($menuDetail)->encode(false)))
                ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
                ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonGroup->render())->encode(false)));
        endforeach;
        echo $dl->primary()->render();

        echo Bleet::input($searchForm, 'pageMenus')
            ->hidden()
            ->fieldData([
                'search-pagination' => 'pageMenus',
            ])
            ->render();
        echo Html::div()
            ->attribute('dboard-search-pagination', true)
            ->content(Widgets::pagination($paginators['menus'], $urlGenerator, $translator)
                ->pageParam('pageMenus')
                ->showInfo()
                ->warning()
                ->render())
            ->encode(false)
            ->render();
    endif;

    if (empty($slugs) === false):
        echo Bleet::hr($translator->translate('Redirections ({count})', [
            'count' => $paginators['slugs']->getTotalItems(),
        ], 'dboard-common'))->accent();

        $dl = Bleet::dl()->cols(4);
        foreach ($slugs as $slug):
            /** @var Slug $slug */
            $editUrl = $urlGenerator->generate('dboard.slugs.edit', [
                'id' => $slug->getId(),
            ]);

            $host = $slug->host;
            $hostName = $host !== null ? $host->getName() : 'localhost';
            $sourceUrl = 'https://' . $hostName . '/' . $slug->getPath();
            $sourceLink = Bleet::a($sourceUrl . ' (#' . $slug->getId() . ')', $editUrl)->render();

            $targetUrl = $slug->getTargetUrl();

            $httpCode = $slug->getHttpCode();
            $httpCodeBadge = Bleet::badge((string) $httpCode)->info()->render();

            $statusBadge = $slug->isActive() === true
                ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
                : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

            $buttonGroup = Bleet::buttonGroup()
                ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->addAttributes($editPopover->trigger())->button());

            $dl = $dl
                ->addItem(Bleet::termItem($translator->translate('Source', category: 'dboard-common'))->addDetail(Bleet::detailItem($sourceLink)->encode(false)))
                ->addItem(Bleet::termItem($translator->translate('Target', category: 'dboard-common'))->detail($targetUrl))
                ->addItem(Bleet::termItem($translator->translate('Code / Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($httpCodeBadge . ' ' . $statusBadge)->encode(false)))
                ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonGroup->render())->encode(false)));
        endforeach;
        echo $dl->primary()->render();
        echo Bleet::input($searchForm, 'pageSlugs')
            ->hidden()
            ->fieldData([
                'search-pagination' => 'pageSlugs',
            ])
            ->render();
        echo Html::div()
            ->attribute('dboard-search-pagination', true)
            ->content(Widgets::pagination($paginators['slugs'], $urlGenerator, $translator)
                ->pageParam('pageSlugs')
                ->showInfo()
                ->accent()
                ->render())
            ->encode(false)
            ->render();
    endif;
    echo $editPopover;
endif;
?>
