<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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
 * @var array<int, Tag> $items
 * @var SearchForm $searchForm
 * @var PageForm $pageForm
 * @var CurrentRoute $currentRoute
 * @var string|null $csrf
 * @var callable $userCan
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
            ->icon('tag')
            ->title($translator->translate('No tag', category: 'dboard-content'))
            ->description($translator->translate('Start by creating a new tag.', category: 'dboard-content'))
            ->button($translator->translate('New tag', category: 'dboard-content'), $urlGenerator->generate('dboard.tags.create'))
            ->primary();
    endif;
else:
    $dl = Bleet::dl()
        ->cols(4)
        ->colTemplateClass('md:grid-cols-[3fr_1fr_1fr_1fr]');

    $canUpdate = $userCan(Rbac::PERMISSION_TAG_UPDATE);
    $canExport = $userCan(Rbac::PERMISSION_TAG_EXPORT);
    $canDelete = $userCan(Rbac::PERMISSION_TAG_DELETE);

    $editPopover = Bleet::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    $deactivatePopover = Bleet::popover($translator->translate('Deactivate', category: 'dboard-common'))->id('list-popover-deactivate');
    $activatePopover = Bleet::popover($translator->translate('Activate', category: 'dboard-common'))->id('list-popover-activate');
    $exportPopover = Bleet::popover($translator->translate('Export', category: 'dboard-common'))->id('list-popover-export');
    $deletePopover = Bleet::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');

    foreach ($items as $tag):
        /** @var Tag $tag */
        $editUrl = $urlGenerator->generate('dboard.tags.edit', [
            'id' => $tag->getId(),
        ]);

        $buttonGroup = Bleet::buttonGroup();

        if ($canUpdate === true):
            $buttonGroup = $buttonGroup->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->addAttributes($editPopover->trigger())->button());

            $toggleUrl = $urlGenerator->generate('dboard.tags.toggle', [
                'id' => $tag->getId(),
            ]);
            $toggleButton = $tag->isActive() === true
                ? Bleet::button()->icon('pause')->warning()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()->addAttributes($deactivatePopover->trigger())
                : Bleet::button()->icon('play')->success()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()->addAttributes($activatePopover->trigger());
            $buttonGroup = $buttonGroup->addButton($toggleButton);
        endif;

        if ($canExport === true):
            $exportUrl = $urlGenerator->generate('dboard.tags.export', [
                'id' => $tag->getId(),
            ]);
            $buttonGroup = $buttonGroup->addButton(Bleet::a()->url($exportUrl)->icon('arrow-down-tray')->secondary()->outline()->xs()->attribute('download', '')->addAttributes($exportPopover->trigger())->button());
        endif;

        if ($canDelete === true):
            $deleteUrl = $urlGenerator->generate('dboard.tags.delete', [
                'id' => $tag->getId(),
            ]);
            $buttonGroup = $buttonGroup->addButton(Bleet::button()->icon('trash')->danger()->outline()->addAttributes(Bleet::modal()->trigger($deleteUrl))->xs()->addAttributes($deletePopover->trigger()));
        endif;

        $tagLink = Bleet::a($tag->getName() . ' (#' . $tag->getId() . ')', $editUrl)->render();

        $tagDetail = $tagLink;

        $statusBadge = $tag->isActive() === true
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
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonGroup->render())->encode(false)));
    endforeach;

    echo $dl->primary()->render();
    echo $editPopover;
    echo $deactivatePopover;
    echo $activatePopover;
    echo $exportPopover;
    echo $deletePopover;
endif;
?>
