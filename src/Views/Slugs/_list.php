<?php

declare(strict_types=1);

/**
 * _list.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

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
$slugs = iterator_to_array($paginator->read());

if (empty($slugs)) {
    if ($searchForm->isSearch()) {
        echo Bleet::emptyState()
            ->icon('magnifying-glass')
            ->title($translator->translate('No results', category: 'dboard-common'))
            ->primary();
    } else {
        echo Bleet::emptyState()
            ->icon('arrow-uturn-right')
            ->title($translator->translate('No redirection', category: 'dboard-modules'))
            ->description($translator->translate('Start by creating a new redirection.', category: 'dboard-modules'))
            ->button($translator->translate('New redirection', category: 'dboard-modules'), $urlGenerator->generate('dboard.slugs.create'))
            ->primary();
    }
} else {
    $dl = Bleet::dl()->cols(4);
    foreach ($slugs as $slug) {
        /** @var Slug $slug */
        $editUrl = $urlGenerator->generate('dboard.slugs.edit', ['id' => $slug->getId()]);
        $toggleUrl = $urlGenerator->generate('dboard.slugs.toggle', ['id' => $slug->getId()]);
        $deleteUrl = $urlGenerator->generate('dboard.slugs.delete', ['id' => $slug->getId()]);

        $toggleButton = $slug->isActive()
            ? Bleet::button()->icon('pause')->warning()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()
                ->attribute('bleet-popover-trigger', 'list-popover-deactivate')
            : Bleet::button()->icon('play')->success()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()
                ->attribute('bleet-popover-trigger', 'list-popover-activate');
        $buttonsBar = Bleet::buttonsBar()
            ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-edit')->button())
            ->addButton(Bleet::button()->icon('trash')->danger()->outline()->addAttributes(Bleet::modal()->trigger($deleteUrl))->xs()->attribute('bleet-popover-trigger', 'list-popover-delete'))
            ->addButton($toggleButton);

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

        $dl = $dl
            ->addItem(Bleet::termItem($translator->translate('Source', category: 'dboard-modules'))->addDetail(Bleet::detailItem($sourceLink)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Target', category: 'dboard-modules'))->detail($targetUrl))
            ->addItem(Bleet::termItem($translator->translate('Code / Status', category: 'dboard-modules'))->addDetail(Bleet::detailItem($httpCodeBadge . ' ' . $statusBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonsBar->render())->encode(false)));
    }

    echo $dl->primary()->render();
    echo Widgets::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    echo Widgets::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
    echo Widgets::popover($translator->translate('Deactivate', category: 'dboard-common'))->id('list-popover-deactivate');
    echo Widgets::popover($translator->translate('Activate', category: 'dboard-common'))->id('list-popover-activate');
}
?>
