<?php

declare(strict_types=1);

/**
 * _list.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\GlobalXeo;
use Blackcube\Dcore\Models\Host;
use Blackcube\Bleet\Bleet;
use Blackcube\Dboard\Widgets\Widgets;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var array<int, array{host: Host, globalXeo: ?GlobalXeo}> $hostData
 * @var string $editRoute
 * @var string $toggleRoute
 * @var string $deleteRoute
 * @var string $kindLabel
 */

?>
<?php
if (empty($hostData)) {
    echo Bleet::emptyState()
        ->icon('globe-alt')
        ->title($translator->translate('No host', category: 'dboard-content'))
        ->description($translator->translate('No host configured.', category: 'dboard-content'))
        ->primary();
} else {
    $dl = Bleet::dl()->cols(3);
    foreach ($hostData as $item) {
        /** @var Host $host */
        $host = $item['host'];
        /** @var ?GlobalXeo $globalXeo */
        $globalXeo = $item['globalXeo'];

        $editUrl = $urlGenerator->generate($editRoute, ['hostId' => $host->getId()]);
        $toggleUrl = $urlGenerator->generate($toggleRoute, ['hostId' => $host->getId()]);
        $deleteUrl = $urlGenerator->generate($deleteRoute, ['hostId' => $host->getId()]);
        $hostLink = Bleet::a('https://' . $host->getName() . '/ (#' . $host->getId() . ')', $editUrl)->render();

        if ($globalXeo !== null) {
            $statusBadge = $globalXeo->isActive()
                ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
                : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

            $toggleButton = $globalXeo->isActive()
                ? Bleet::button()->icon('pause')->warning()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->attribute('bleet-popover-trigger', 'list-popover-deactivate')->xs()
                : Bleet::button()->icon('play')->success()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->attribute('bleet-popover-trigger', 'list-popover-activate')->xs();

            $deleteButton = Bleet::button()->icon('trash')->danger()->outline()->addAttributes(Bleet::modal()->trigger($deleteUrl))->attribute('bleet-popover-trigger', 'list-popover-delete')->xs();

            $actions = Bleet::buttonsBar()
                ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-edit')->button())
                ->addButton($deleteButton)
                ->addButton($toggleButton)
                ->render();
        } else {
            $statusBadge = Bleet::badge($translator->translate('Not configured', category: 'dboard-content'))->secondary()->render();
            $actions = Bleet::buttonsBar()
                ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-edit')->button())
                ->addButton(Bleet::button()->icon('trash')->danger()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-delete')->disabled())
                ->addButton(Bleet::button()->icon('play')->success()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-activate')->disabled())
                ->render();
        }

        $dl = $dl
            ->addItem(Bleet::termItem($translator->translate('Host', category: 'dboard-content'))->addDetail(Bleet::detailItem($hostLink)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($actions)->encode(false)));
    }
    echo $dl->primary()->render();
    echo Widgets::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    echo Widgets::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
    echo Widgets::popover($translator->translate('Deactivate', category: 'dboard-common'))->id('list-popover-deactivate');
    echo Widgets::popover($translator->translate('Activate', category: 'dboard-common'))->id('list-popover-activate');
}
?>
