<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dcore\Models\Menu;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\PageForm;
use Blackcube\Dboard\Models\Forms\SearchForm;
use Blackcube\Html\Aurelia;
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
 * @var array<int, Menu> $items
 * @var SearchForm $searchForm
 * @var PageForm $pageForm
 * @var CurrentRoute $currentRoute
 * @var string|null $csrf
 */

?>
<?php
$items = $items ?? iterator_to_array($paginator->read());

if (empty($items) === true):
    if ($searchForm->isSearch() === true):
        echo Bleet::emptyState()
            ->icon('magnifying-glass')
            ->title($translator->translate('No results', category: 'dboard-common'))
            ->primary();
    else:
        echo Bleet::emptyState()
            ->icon('bars-3')
            ->title($translator->translate('No menu', category: 'dboard-content'))
            ->description($translator->translate('Start by creating a new menu.', category: 'dboard-content'))
            ->button($translator->translate('New menu', category: 'dboard-content'), $urlGenerator->generate('dboard.menus.create'))
            ->primary();
    endif;
else:
    $treeMoveUrl = $urlGenerator->generate('dboard.menus.move');
    $dl = Bleet::dl()
        ->cols(3)
        ->attribute('dboard-tree-drag-drop', Aurelia::attributesCustomAttribute([
            'url' => $treeMoveUrl,
            'csrf' => $csrf,
            'errorTitle' => $translator->translate('Error', category: 'dboard-common'),
            'errorContent' => $translator->translate('Move failed.', category: 'dboard-common'),
        ]));

    $editPopover = Bleet::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    $deletePopover = Bleet::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
    $deactivatePopover = Bleet::popover($translator->translate('Deactivate', category: 'dboard-common'))->id('list-popover-deactivate');
    $activatePopover = Bleet::popover($translator->translate('Activate', category: 'dboard-common'))->id('list-popover-activate');

    foreach ($items as $menu):
        /** @var Menu $menu */
        $editUrl = $urlGenerator->generate('dboard.menus.edit', [
            'id' => $menu->getId(),
        ]);
        $toggleUrl = $urlGenerator->generate('dboard.menus.toggle', [
            'id' => $menu->getId(),
        ]);
        $deleteUrl = $urlGenerator->generate('dboard.menus.delete', [
            'id' => $menu->getId(),
        ]);

        $toggleButton = $menu->isActive() === true
            ? Bleet::button()->icon('pause')->warning()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()->addAttributes($deactivatePopover->trigger())
            : Bleet::button()->icon('play')->success()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()->addAttributes($activatePopover->trigger());

        $buttonGroup = Bleet::buttonGroup()
            ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->addAttributes($editPopover->trigger())->button())
            ->addButton(Bleet::button()->icon('trash')->danger()->outline()->addAttributes(Bleet::modal()->trigger($deleteUrl))->xs()->addAttributes($deletePopover->trigger()))
            ->addButton($toggleButton);

        $handleIcon = Bleet::svg()->solid('bars-3')->addClass('size-4', 'text-secondary-400')->render();
        $handle = Html::tag('span', $handleIcon, [
            'data-tree-drag-drop' => 'handle',
            'class' => [
                'inline-flex',
                'items-center',
                'mr-2',
            ],
        ])->encode(false)->render();

        $menuLink = Bleet::a($menu->getName() . ' (#' . $menu->getId() . ')', $editUrl)->render();

        $host = $menu->host;
        $hostLabel = $host !== null ? 'https://' . $host->getName() : $translator->translate('All domains', category: 'dboard-common');

        $languageIcon = Bleet::svg()->outline('language')->addClass('size-4', 'text-secondary-400')->render();
        $languageText = Html::tag('span', Html::encode($menu->getLanguageId()), [
            'class' => [
                'text-sm',
                'text-secondary-600',
            ],
        ])
            ->encode(false)
            ->render();

        $hostIcon = Bleet::svg()->outline('server')->addClass('size-4', 'text-secondary-400', 'ml-2')->render();
        $hostText = Html::tag('span', Html::encode($hostLabel), [
            'class' => [
                'text-sm',
                'text-secondary-600',
            ],
        ])
            ->encode(false)
            ->render();

        $infoLine = Html::tag('div', $languageIcon . $languageText . $hostIcon . $hostText, [
            'class' => [
                'flex',
                'items-center',
                'gap-1',
                'mt-1',
            ],
        ])
            ->encode(false)
            ->render();

        $menuDetail = $handle . $menuLink . $infoLine;

        $statusBadge = $menu->isActive() === true
            ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
            : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

        $dl = $dl
            ->addItem(
                Bleet::termItem($translator->translate('Menu', category: 'dboard-content'))
                    ->level($menu->getLevel())
                    ->rowAttributes([
                        'data-tree-drag-drop' => 'item-' . $menu->getId(),
                    ])
                    ->addDetail(Bleet::detailItem($menuDetail)->encode(false))
            )
            ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonGroup->render())->encode(false)));
    endforeach;

    echo $dl->primary()->render();
    echo $editPopover;
    echo $deletePopover;
    echo $deactivatePopover;
    echo $activatePopover;
endif;
?>
