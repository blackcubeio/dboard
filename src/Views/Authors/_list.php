<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Author;
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
 * @var OffsetPaginator $paginator
 * @var CurrentRoute $currentRoute
 */

?>
<?php
$authors = iterator_to_array($paginator->read());

if (empty($authors) === true):
    if ($searchForm->isSearch() === true):
        echo Bleet::emptyState()
            ->icon('magnifying-glass')
            ->title($translator->translate('No results', category: 'dboard-common'))
            ->primary();
    else:
        echo Bleet::emptyState()
            ->icon('users')
            ->title($translator->translate('No author', category: 'dboard-modules'))
            ->description($translator->translate('Start by creating a new author.', category: 'dboard-modules'))
            ->button($translator->translate('New author', category: 'dboard-modules'), $urlGenerator->generate('dboard.authors.create'))
            ->primary();
    endif;
else:
    $dl = Bleet::dl()->cols(4);
    $editPopover = Bleet::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    $deletePopover = Bleet::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
    $deactivatePopover = Bleet::popover($translator->translate('Deactivate', category: 'dboard-common'))->id('list-popover-deactivate');
    $activatePopover = Bleet::popover($translator->translate('Activate', category: 'dboard-common'))->id('list-popover-activate');
    foreach ($authors as $author):
        /** @var Author $author */
        $fullName = Html::encode($author->getFirstname() . ' ' . $author->getLastname());

        $editUrl = $urlGenerator->generate('dboard.authors.edit', [
            'id' => $author->getId(),
        ]);
        $toggleUrl = $urlGenerator->generate('dboard.authors.toggle', [
            'id' => $author->getId(),
        ]);
        $deleteUrl = $urlGenerator->generate('dboard.authors.delete', [
            'id' => $author->getId(),
        ]);

        $editButton = Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->addAttributes($editPopover->trigger())->button();
        $deleteButton = Bleet::button()->icon('trash')->danger()->outline()->xs()
            ->addAttributes(Bleet::modal()->trigger($deleteUrl))
            ->addAttributes($deletePopover->trigger());
        $toggleButton = $author->isActive() === true
            ? Bleet::button()->icon('pause')->warning()->outline()->xs()
                ->addAttributes(Bleet::modal()->trigger($toggleUrl))
                ->addAttributes($deactivatePopover->trigger())
            : Bleet::button()->icon('play')->success()->outline()->xs()
                ->addAttributes(Bleet::modal()->trigger($toggleUrl))
                ->addAttributes($activatePopover->trigger());

        $buttonGroup = Bleet::buttonGroup()
            ->addButton($editButton)
            ->addButton($deleteButton)
            ->addButton($toggleButton);

        $authorLink = Bleet::a($fullName, $editUrl)->render();

        $statusBadge = $author->isActive() === true
            ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
            : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

        $jobTitle = $author->getJobTitle() ?? '—';

        $dl = $dl
            ->addItem(Bleet::termItem($translator->translate('Author', category: 'dboard-modules'))->addDetail(Bleet::detailItem($authorLink)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Job title', category: 'dboard-modules'))->detail($jobTitle))
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
