<?php

declare(strict_types=1);

/**
 * _list_flat.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Components\Rbac;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\PageForm;
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
 * @var Administrator $administrator
 * @var ActiveQueryPaginator $paginator
 * @var SearchForm $searchForm
 * @var PageForm $pageForm
 * @var CurrentRoute $currentRoute
 * @var string|null $csrf
 * @var callable $userCan
 */

?>
<?php
$items = iterator_to_array($paginator->read());

if (empty($items)) {
    if ($searchForm->isSearch()) {
        echo Bleet::emptyState()
            ->icon('magnifying-glass')
            ->title($translator->translate('No results', category: 'dboard-common'))
            ->primary();
    } else {
        echo Bleet::emptyState()
            ->icon('tag')
            ->title($translator->translate('No tag', category: 'dboard-content'))
            ->description($translator->translate('Start by creating a new tag.', category: 'dboard-content'))
            ->button($translator->translate('New tag', category: 'dboard-content'), $urlGenerator->generate('dboard.tags.create'))
            ->primary();
    }
} else {
    $dl = Bleet::dl()
        ->cols(4)
        ->colTemplateClass('md:grid-cols-[3fr_1fr_1fr_1fr]');

    foreach ($items as $tag) {
        /** @var Tag $tag */
        $editUrl = $urlGenerator->generate('dboard.tags.edit', ['id' => $tag->getId()]);

        $buttonsBar = Bleet::buttonsBar();

        // Edit button
        if ($userCan(Rbac::PERMISSION_TAG_UPDATE)) {
            $buttonsBar = $buttonsBar->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-edit')->button());

            // Toggle button
            $toggleUrl = $urlGenerator->generate('dboard.tags.toggle', ['id' => $tag->getId()]);
            $toggleButton = $tag->isActive()
                ? Bleet::button()->icon('pause')->warning()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()->attribute('bleet-popover-trigger', 'list-popover-deactivate')
                : Bleet::button()->icon('play')->success()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()->attribute('bleet-popover-trigger', 'list-popover-activate');
            $buttonsBar = $buttonsBar->addButton($toggleButton);
        }

        // Export button
        if ($userCan(Rbac::PERMISSION_TAG_EXPORT)) {
            $exportUrl = $urlGenerator->generate('dboard.tags.export', ['id' => $tag->getId()]);
            $buttonsBar = $buttonsBar->addButton(Bleet::a()->url($exportUrl)->icon('arrow-down-tray')->secondary()->outline()->xs()->attribute('download', '')->attribute('bleet-popover-trigger', 'list-popover-export')->button());
        }

        // Delete button
        if ($userCan(Rbac::PERMISSION_TAG_DELETE)) {
            $deleteUrl = $urlGenerator->generate('dboard.tags.delete', ['id' => $tag->getId()]);
            $buttonsBar = $buttonsBar->addButton(Bleet::button()->icon('trash')->danger()->outline()->addAttributes(Bleet::modal()->trigger($deleteUrl))->xs()->attribute('bleet-popover-trigger', 'list-popover-delete'));
        }

        $tagLink = Bleet::a($tag->getName() . ' (#' . $tag->getId() . ')', $editUrl)->render();

        $tagDetail = $tagLink;

        $statusBadge = $tag->isActive()
            ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
            : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

        $dateUpdate = $tag->getDateUpdate();
        $dateLabel = $dateUpdate !== null
            ? Html::encode((new \IntlDateFormatter($translator->getLocale(), \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT))->format($dateUpdate))
            : '';

        $dl = $dl
            ->addItem(
                Bleet::termItem($translator->translate('Tag', category: 'dboard-content'))
                    ->addDetail(Bleet::detailItem($tagDetail)->encode(false))
            )
            ->addItem(Bleet::termItem($translator->translate('Updated', category: 'dboard-common'))->addDetail(Bleet::detailItem($dateLabel)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonsBar->render())->encode(false)));
    }

    echo $dl->primary()->render();
    echo Widgets::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    echo Widgets::popover($translator->translate('Deactivate', category: 'dboard-common'))->id('list-popover-deactivate');
    echo Widgets::popover($translator->translate('Activate', category: 'dboard-common'))->id('list-popover-activate');
    echo Widgets::popover($translator->translate('Export', category: 'dboard-common'))->id('list-popover-export');
    echo Widgets::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
}
?>
