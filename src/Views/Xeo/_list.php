<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\GlobalXeo;
use Blackcube\Dcore\Models\Host;
use Blackcube\Bleet\Bleet;
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
if (empty($hostData) === true):
    echo Bleet::emptyState()
        ->icon('globe-alt')
        ->title($translator->translate('No host', category: 'dboard-content'))
        ->description($translator->translate('No host configured.', category: 'dboard-content'))
        ->primary();
else:
    $dl = Bleet::dl()->cols(3);
    $editPopover = Bleet::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    $deletePopover = Bleet::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
    $deactivatePopover = Bleet::popover($translator->translate('Deactivate', category: 'dboard-common'))->id('list-popover-deactivate');
    $activatePopover = Bleet::popover($translator->translate('Activate', category: 'dboard-common'))->id('list-popover-activate');
    foreach ($hostData as $hostEntry):
        /** @var Host $host */
        $host = $hostEntry['host'];
        /** @var ?GlobalXeo $globalXeo */
        $globalXeo = $hostEntry['globalXeo'];

        $editUrl = $urlGenerator->generate($editRoute, [
            'hostId' => $host->getId(),
        ]);
        $toggleUrl = $urlGenerator->generate($toggleRoute, [
            'hostId' => $host->getId(),
        ]);
        $deleteUrl = $urlGenerator->generate($deleteRoute, [
            'hostId' => $host->getId(),
        ]);
        $hostLink = Bleet::a('https://' . $host->getName() . '/ (#' . $host->getId() . ')', $editUrl)->render();

        if ($globalXeo !== null):
            $statusBadge = $globalXeo->isActive() === true
                ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
                : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

            $toggleButton = $globalXeo->isActive() === true
                ? Bleet::button()->icon('pause')->warning()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->addAttributes($deactivatePopover->trigger())->xs()
                : Bleet::button()->icon('play')->success()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->addAttributes($activatePopover->trigger())->xs();

            $deleteButton = Bleet::button()->icon('trash')->danger()->outline()->addAttributes(Bleet::modal()->trigger($deleteUrl))->addAttributes($deletePopover->trigger())->xs();

            $actions = Bleet::buttonGroup()
                ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->addAttributes($editPopover->trigger())->button())
                ->addButton($deleteButton)
                ->addButton($toggleButton)
                ->render();
        else:
            $statusBadge = Bleet::badge($translator->translate('Not configured', category: 'dboard-content'))->secondary()->render();
            $actions = Bleet::buttonGroup()
                ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->addAttributes($editPopover->trigger())->button())
                ->addButton(Bleet::button()->icon('trash')->danger()->outline()->xs()->addAttributes($deletePopover->trigger())->disabled())
                ->addButton(Bleet::button()->icon('play')->success()->outline()->xs()->addAttributes($activatePopover->trigger())->disabled())
                ->render();
        endif;

        $dl = $dl
            ->addItem(Bleet::termItem($translator->translate('Host', category: 'dboard-content'))->addDetail(Bleet::detailItem($hostLink)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($actions)->encode(false)));
    endforeach;
    echo $dl->primary()->render();
    echo $editPopover;
    echo $deletePopover;
    echo $deactivatePopover;
    echo $activatePopover;
endif;
?>
