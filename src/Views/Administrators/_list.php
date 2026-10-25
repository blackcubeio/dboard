<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

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
 * @var OffsetPaginator $paginator
 * @var CurrentRoute $currentRoute
 * @var int|null $currentUserId
 */

?>
<?php
$administrators = iterator_to_array($paginator->read());

if (empty($administrators) === true):
    if ($searchForm->isSearch() === true):
        echo Bleet::emptyState()
            ->icon('magnifying-glass')
            ->title($translator->translate('No results', category: 'dboard-common'))
            ->primary();
    else:
        echo Bleet::emptyState()
            ->icon('users')
            ->title($translator->translate('No administrator', category: 'dboard-modules'))
            ->description($translator->translate('Start by creating a new administrator.', category: 'dboard-modules'))
            ->button($translator->translate('New administrator', category: 'dboard-modules'), $urlGenerator->generate('dboard.administrators.create'))
            ->primary();
    endif;
else:
    $dl = Bleet::dl()->cols(4);
    $editPopover = Bleet::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    $permissionsPopover = Bleet::popover($translator->translate('Permissions', category: 'dboard-common'))->id('list-popover-permissions');
    $deletePopover = Bleet::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
    $deactivatePopover = Bleet::popover($translator->translate('Deactivate', category: 'dboard-common'))->id('list-popover-deactivate');
    $activatePopover = Bleet::popover($translator->translate('Activate', category: 'dboard-common'))->id('list-popover-activate');
    foreach ($administrators as $admin):
        /** @var Administrator $admin */
        $isSelf = $admin->getId() === $currentUserId;

        $editUrl = $urlGenerator->generate('dboard.administrators.edit', [
            'id' => $admin->getId(),
        ]);
        $toggleUrl = $urlGenerator->generate('dboard.administrators.toggle', [
            'id' => $admin->getId(),
        ]);
        $deleteUrl = $urlGenerator->generate('dboard.administrators.delete', [
            'id' => $admin->getId(),
        ]);
        $permissionsUrl = $urlGenerator->generate('dboard.administrators.permissions', [
            'id' => $admin->getId(),
        ]);

        $deleteButton = Bleet::button()->icon('trash')->danger()->outline()->xs()->addAttributes($deletePopover->trigger());
        $toggleButton = $admin->isActive() === true
            ? Bleet::button()->icon('pause')->warning()->outline()->xs()->addAttributes($deactivatePopover->trigger())
            : Bleet::button()->icon('play')->success()->outline()->xs()->addAttributes($activatePopover->trigger());
        $permissionsButton = Bleet::button()->icon('shield-check')->secondary()->outline()->xs()->addAttributes($permissionsPopover->trigger());

        if ($isSelf === true):
            $editButton = Bleet::button()->icon('pencil')->info()->outline()->xs()->addAttributes($editPopover->trigger())->disabled();
            $deleteButton = $deleteButton->disabled();
            $toggleButton = $toggleButton->disabled();
            $permissionsButton = $permissionsButton->disabled();
        else:
            $editButton = Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->addAttributes($editPopover->trigger())->button();
            $deleteButton = $deleteButton->addAttributes(Bleet::modal()->trigger($deleteUrl));
            $toggleButton = $toggleButton->addAttributes(Bleet::modal()->trigger($toggleUrl));
            $permissionsButton = $permissionsButton->addAttributes(Bleet::drawer()->trigger($permissionsUrl));
        endif;

        $buttonGroup = Bleet::buttonGroup()
            ->addButton($editButton)
            ->addButton($permissionsButton)
            ->addButton($deleteButton)
            ->addButton($toggleButton);

        $adminLink = $isSelf === true
            ? Html::encode($admin->getName())
            : Bleet::a($admin->getName(), $editUrl)->render();

        $statusBadge = $admin->isActive() === true
            ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
            : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

        $dl = $dl
            ->addItem(Bleet::termItem($translator->translate('Administrator', category: 'dboard-modules'))->addDetail(Bleet::detailItem($adminLink)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Email', category: 'dboard-modules'))->detail($admin->getEmail()))
            ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonGroup->render())->encode(false)));
    endforeach;

    echo $dl->primary()->render();
    echo $editPopover;
    echo $permissionsPopover;
    echo $deletePopover;
    echo $deactivatePopover;
    echo $activatePopover;
endif;
?>

