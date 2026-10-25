<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Type;
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
 * @var array<int, Type> $items
 * @var array<int, true> $usedTypeIds
 * @var \Blackcube\Dboard\Models\Forms\SearchForm $searchForm
 * @var CurrentRoute $currentRoute
 */

?>
<?php
$types = $items ?? iterator_to_array($paginator->read());

if (empty($types) === true):
    if ($searchForm->isSearch() === true):
        echo Bleet::emptyState()
            ->icon('magnifying-glass')
            ->title($translator->translate('No results', category: 'dboard-common'))
            ->primary();
    else:
        echo Bleet::emptyState()
            ->icon('rectangle-stack')
            ->title($translator->translate('No type', category: 'dboard-modules'))
            ->description($translator->translate('Start by creating a new type.', category: 'dboard-modules'))
            ->button($translator->translate('New type', category: 'dboard-modules'), $urlGenerator->generate('dboard.types.create'))
            ->primary();
    endif;
else:
    $dl = Bleet::dl()->cols(5);
    $editPopover = Bleet::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    $deletePopover = Bleet::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
    foreach ($types as $type):
        /** @var Type $type */
        $isUsed = isset($usedTypeIds[$type->getId()]) === true;

        $editUrl = $urlGenerator->generate('dboard.types.edit', [
            'id' => $type->getId(),
        ]);
        $deleteUrl = $urlGenerator->generate('dboard.types.delete', [
            'id' => $type->getId(),
        ]);

        $deleteButton = Bleet::button()->icon('trash')->danger()->outline()->xs()
            ->addAttributes($deletePopover->trigger());
        if ($isUsed === true):
            $deleteButton = $deleteButton->disabled();
        else:
            $deleteButton = $deleteButton->addAttributes(Bleet::modal()->trigger($deleteUrl));
        endif;

        $buttonGroup = Bleet::buttonGroup()
            ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->addAttributes($editPopover->trigger())->button())
            ->addButton($deleteButton);

        $nameLink = Bleet::a($type->getName() . ' (#' . $type->getId() . ')', $editUrl)->render();

        $contentBadge = $type->isContentAllowed() === true
            ? Bleet::badge($translator->translate('Yes', category: 'dboard-common'))->success()->render()
            : Bleet::badge($translator->translate('No', category: 'dboard-common'))->danger()->render();

        $tagBadge = $type->isTagAllowed() === true
            ? Bleet::badge($translator->translate('Yes', category: 'dboard-common'))->success()->render()
            : Bleet::badge($translator->translate('No', category: 'dboard-common'))->danger()->render();

        $dl = $dl
            ->addItem(Bleet::termItem($translator->translate('Name', category: 'dboard-modules'))->addDetail(Bleet::detailItem($nameLink)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Handler', category: 'dboard-modules'))->detail($type->getHandler() ?? '-'))
            ->addItem(Bleet::termItem($translator->translate('Content', category: 'dboard-modules'))->addDetail(Bleet::detailItem($contentBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Tag', category: 'dboard-modules'))->addDetail(Bleet::detailItem($tagBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonGroup->render())->encode(false)));
    endforeach;

    echo $dl->primary()->render();
    echo $editPopover;
    echo $deletePopover;
endif;
?>
