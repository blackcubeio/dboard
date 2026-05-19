<?php

declare(strict_types=1);

/**
 * _list.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Host;
use Blackcube\Dcore\Models\Menu;
use Blackcube\Dcore\Models\Slug;
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
 * @var CurrentRoute $currentRoute
 */

?>
<?php
$hosts = iterator_to_array($paginator->read());

if (empty($hosts)) {
    if ($searchForm->isSearch()) {
        echo Bleet::emptyState()
            ->icon('magnifying-glass')
            ->title($translator->translate('No results', category: 'dboard-common'))
            ->primary();
    } else {
        echo Bleet::emptyState()
            ->icon('globe-alt')
            ->title($translator->translate('No host', category: 'dboard-modules'))
            ->description($translator->translate('Start by creating a new host.', category: 'dboard-modules'))
            ->button($translator->translate('New host', category: 'dboard-modules'), $urlGenerator->generate('dboard.hosts.create'))
            ->primary();
    }
} else {
    $dl = Bleet::dl()->cols(3);
    foreach ($hosts as $host) {
        /** @var Host $host */
        $isProtected = $host->getId() === 1;
        $isUsed = !$isProtected && (
            Slug::query()->andWhere(['hostId' => $host->getId()])->exists() ||
            Menu::query()->andWhere(['hostId' => $host->getId()])->exists()
        );

        $editUrl = $urlGenerator->generate('dboard.hosts.edit', ['id' => $host->getId()]);
        $toggleUrl = $urlGenerator->generate('dboard.hosts.toggle', ['id' => $host->getId()]);
        $deleteUrl = $urlGenerator->generate('dboard.hosts.delete', ['id' => $host->getId()]);

        $toggleButton = $host->isActive()
            ? Bleet::button()->icon('pause')->warning()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()
                ->attribute('bleet-popover-trigger', 'list-popover-deactivate')
            : Bleet::button()->icon('play')->success()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()
                ->attribute('bleet-popover-trigger', 'list-popover-activate');

        $deleteButton = Bleet::button()->icon('trash')->danger()->outline()->xs()
            ->attribute('bleet-popover-trigger', 'list-popover-delete');
        if ($isUsed) {
            $deleteButton = $deleteButton->disabled();
        } else {
            $deleteButton = $deleteButton->addAttributes(Bleet::modal()->trigger($deleteUrl));
        }

        $buttonsBar = Bleet::buttonsBar()
            ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-edit')->button())
            ->addButton($deleteButton)
            ->addButton($toggleButton);

        $hostLink = Bleet::a('https://' . $host->getName() . '/ (#' . $host->getId() . ')', $editUrl)->render();

        $statusBadge = $host->isActive()
            ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
            : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

        if ($isProtected) {
            $statusBadge .= ' ' . Bleet::badge($translator->translate('Protected', category: 'dboard-modules'))->secondary()->render();
        }

        $dl = $dl
            ->addItem(Bleet::termItem($translator->translate('Host', category: 'dboard-modules'))->addDetail(Bleet::detailItem($hostLink)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($isProtected ? '-' : $buttonsBar->render())->encode(false)));
    }

    echo $dl->primary()->render();
    echo Widgets::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    echo Widgets::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
    echo Widgets::popover($translator->translate('Deactivate', category: 'dboard-common'))->id('list-popover-deactivate');
    echo Widgets::popover($translator->translate('Activate', category: 'dboard-common'))->id('list-popover-activate');
}
?>
