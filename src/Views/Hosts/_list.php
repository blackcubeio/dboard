<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Host;
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
 * @var array<int, Host> $items
 * @var array<int, true> $usedHostIds
 * @var \Blackcube\Dboard\Models\Forms\SearchForm $searchForm
 * @var CurrentRoute $currentRoute
 */

?>
<?php
$hosts = $items ?? iterator_to_array($paginator->read());

if (empty($hosts) === true):
    if ($searchForm->isSearch() === true):
        echo Bleet::emptyState()
            ->icon('magnifying-glass')
            ->title($translator->translate('No results', category: 'dboard-common'))
            ->primary();
    else:
        echo Bleet::emptyState()
            ->icon('globe-alt')
            ->title($translator->translate('No host', category: 'dboard-modules'))
            ->description($translator->translate('Start by creating a new host.', category: 'dboard-modules'))
            ->button($translator->translate('New host', category: 'dboard-modules'), $urlGenerator->generate('dboard.hosts.create'))
            ->primary();
    endif;
else:
    $dl = Bleet::dl()->cols(3);
    $editPopover = Bleet::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    $deletePopover = Bleet::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
    $deactivatePopover = Bleet::popover($translator->translate('Deactivate', category: 'dboard-common'))->id('list-popover-deactivate');
    $activatePopover = Bleet::popover($translator->translate('Activate', category: 'dboard-common'))->id('list-popover-activate');
    foreach ($hosts as $host):
        /** @var Host $host */
        $isProtected = $host->getId() === 1;
        $isUsed = $isProtected === false && isset($usedHostIds[$host->getId()]) === true;

        $editUrl = $urlGenerator->generate('dboard.hosts.edit', [
            'id' => $host->getId(),
        ]);
        $toggleUrl = $urlGenerator->generate('dboard.hosts.toggle', [
            'id' => $host->getId(),
        ]);
        $deleteUrl = $urlGenerator->generate('dboard.hosts.delete', [
            'id' => $host->getId(),
        ]);

        $toggleButton = $host->isActive() === true
            ? Bleet::button()->icon('pause')->warning()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()
                ->addAttributes($deactivatePopover->trigger())
            : Bleet::button()->icon('play')->success()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()
                ->addAttributes($activatePopover->trigger());

        $deleteButton = Bleet::button()->icon('trash')->danger()->outline()->xs()
            ->addAttributes($deletePopover->trigger());
        if ($isUsed === true):
            $deleteButton = $deleteButton->disabled();
        else:
            $deleteButton = $deleteButton->addAttributes(Bleet::modal()->trigger($deleteUrl));
        endif;

        $buttonGroup = Bleet::buttonGroup()
            ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->addAttributes($editPopover->trigger())->button())
            ->addButton($deleteButton)
            ->addButton($toggleButton);

        $hostLink = Bleet::a('https://' . $host->getName() . '/ (#' . $host->getId() . ')', $editUrl)->render();

        $statusBadge = $host->isActive() === true
            ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
            : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

        if ($isProtected === true):
            $statusBadge .= ' ' . Bleet::badge($translator->translate('Protected', category: 'dboard-modules'))->secondary()->render();
        endif;

        $dl = $dl
            ->addItem(Bleet::termItem($translator->translate('Host', category: 'dboard-modules'))->addDetail(Bleet::detailItem($hostLink)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($isProtected === true ? '-' : $buttonGroup->render())->encode(false)));
    endforeach;

    echo $dl->primary()->render();
    echo $editPopover;
    echo $deletePopover;
    echo $deactivatePopover;
    echo $activatePopover;
endif;
?>
