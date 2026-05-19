<?php

declare(strict_types=1);

/**
 * _list.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

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
 * @var OffsetPaginator $paginator
 * @var CurrentRoute $currentRoute
 * @var int|null $currentUserId
 */

?>
<?php
$administrators = iterator_to_array($paginator->read());

if (empty($administrators)) {
    if ($searchForm->isSearch()) {
        echo Bleet::emptyState()
            ->icon('magnifying-glass')
            ->title($translator->translate('No results', category: 'dboard-common'))
            ->primary();
    } else {
        echo Bleet::emptyState()
            ->icon('users')
            ->title($translator->translate('No administrator', category: 'dboard-modules'))
            ->description($translator->translate('Start by creating a new administrator.', category: 'dboard-modules'))
            ->button($translator->translate('New administrator', category: 'dboard-modules'), $urlGenerator->generate('dboard.administrators.create'))
            ->primary();
    }
} else {
    $dl = Bleet::dl()->cols(4);
    foreach ($administrators as $admin) {
        /** @var Administrator $admin */
        $isSelf = $admin->getId() === $currentUserId;

        $editUrl = $urlGenerator->generate('dboard.administrators.edit', ['id' => $admin->getId()]);
        $toggleUrl = $urlGenerator->generate('dboard.administrators.toggle', ['id' => $admin->getId()]);
        $deleteUrl = $urlGenerator->generate('dboard.administrators.delete', ['id' => $admin->getId()]);
        $permissionsUrl = $urlGenerator->generate('dboard.administrators.permissions', ['id' => $admin->getId()]);

        $deleteButton = Bleet::button()->icon('trash')->danger()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-delete');
        $toggleButton = $admin->isActive()
            ? Bleet::button()->icon('pause')->warning()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-deactivate')
            : Bleet::button()->icon('play')->success()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-activate');
        $permissionsButton = Bleet::button()->icon('shield-check')->secondary()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-permissions');

        if ($isSelf) {
            $editButton = Bleet::button()->icon('pencil')->info()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-edit')->disabled();
            $deleteButton = $deleteButton->disabled();
            $toggleButton = $toggleButton->disabled();
            $permissionsButton = $permissionsButton->disabled();
        } else {
            $editButton = Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-edit')->button();
            $deleteButton = $deleteButton->addAttributes(Bleet::modal()->trigger($deleteUrl));
            $toggleButton = $toggleButton->addAttributes(Bleet::modal()->trigger($toggleUrl));
            $permissionsButton = $permissionsButton->addAttributes(Bleet::drawer()->trigger($permissionsUrl));
        }

        $buttonsBar = Bleet::buttonsBar()
            ->addButton($editButton)
            ->addButton($permissionsButton)
            ->addButton($deleteButton)
            ->addButton($toggleButton);

        $adminLink = $isSelf
            ? Html::encode($admin->getName())
            : Bleet::a($admin->getName(), $editUrl)->render();

        $statusBadge = $admin->isActive()
            ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
            : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

        $dl = $dl
            ->addItem(Bleet::termItem($translator->translate('Administrator', category: 'dboard-modules'))->addDetail(Bleet::detailItem($adminLink)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Email', category: 'dboard-modules'))->detail($admin->getEmail()))
            ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonsBar->render())->encode(false)));
    }

    echo $dl->primary()->render();
    echo Widgets::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    echo Widgets::popover($translator->translate('Permissions', category: 'dboard-common'))->id('list-popover-permissions');
    echo Widgets::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
    echo Widgets::popover($translator->translate('Deactivate', category: 'dboard-common'))->id('list-popover-deactivate');
    echo Widgets::popover($translator->translate('Activate', category: 'dboard-common'))->id('list-popover-activate');
}
?>

