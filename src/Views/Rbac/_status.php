<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
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

$statusBadge = $isInSync === true
    ? Bleet::badge($translator->translate('Synchronized', category: 'dboard-modules'))->success()->render()
    : Bleet::badge($translator->translate('Out of sync', category: 'dboard-modules'))->danger()->render();

$refreshPopover = Bleet::popover($translator->translate('Refresh', category: 'dboard-common'))->id('rbac-popover-refresh');
$viewPopover = Bleet::popover($translator->translate('View', category: 'dboard-common'))->id('rbac-popover-view');

$refreshButton = Bleet::button()
    ->icon('arrow-path')
    ->warning()
    ->outline()
    ->xs()
    ->addAttributes(Bleet::modal()->trigger($refreshUrl))
    ->addAttributes($refreshPopover->trigger());

$viewButton = Bleet::a()
    ->url($viewUrl)
    ->icon('eye')
    ->info()
    ->outline()
    ->xs()
    ->addAttributes($viewPopover->trigger())
    ->button();

$buttonGroup = Bleet::buttonGroup()
    ->addButton($refreshButton)
    ->addButton($viewButton);

?>
<?php
$dl = Bleet::dl()->cols(3);
$dl = $dl
    ->addItem(Bleet::termItem($translator->translate('Roles & Permissions', category: 'dboard-modules'))->addDetail(Bleet::detailItem($translator->translate('RBAC synchronization code ↔ database', category: 'dboard-modules'))))
    ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
    ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonGroup->render())->encode(false)));

echo $dl->primary()->render();
echo $refreshPopover;
echo $viewPopover;
?>
