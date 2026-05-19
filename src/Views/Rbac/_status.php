<?php

declare(strict_types=1);

/**
 * _status.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Blackcube\Dboard\Widgets\Widgets;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var bool $isInSync
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var CurrentRoute $currentRoute
 */

$refreshUrl = $urlGenerator->generate('dboard.rbac.refresh');
$viewUrl = $urlGenerator->generate('dboard.rbac.view');

$statusBadge = $isInSync
    ? Bleet::badge($translator->translate('Synchronized', category: 'dboard-modules'))->success()->render()
    : Bleet::badge($translator->translate('Out of sync', category: 'dboard-modules'))->danger()->render();

$refreshButton = Bleet::button()
    ->icon('arrow-path')
    ->warning()
    ->outline()
    ->xs()
    ->addAttributes(Bleet::modal()->trigger($refreshUrl))
    ->attribute('bleet-popover-trigger', 'rbac-popover-refresh');

$viewButton = Bleet::a()
    ->url($viewUrl)
    ->icon('eye')
    ->info()
    ->outline()
    ->xs()
    ->attribute('bleet-popover-trigger', 'rbac-popover-view')
    ->button();

$buttonsBar = Bleet::buttonsBar()
    ->addButton($refreshButton)
    ->addButton($viewButton);

?>
<?php
$dl = Bleet::dl()->cols(3);
$dl = $dl
    ->addItem(Bleet::termItem($translator->translate('Roles & Permissions', category: 'dboard-modules'))->addDetail(Bleet::detailItem($translator->translate('RBAC synchronization code ↔ database', category: 'dboard-modules'))))
    ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
    ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonsBar->render())->encode(false)));

echo $dl->primary()->render();
echo Widgets::popover($translator->translate('Refresh', category: 'dboard-common'))->id('rbac-popover-refresh');
echo Widgets::popover($translator->translate('View', category: 'dboard-common'))->id('rbac-popover-view');
?>
