<?php

declare(strict_types=1);

/**
 * _list.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Parameter;
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
 * @var Administrator $administrator
 * @var OffsetPaginator $paginator
 * @var \Blackcube\Dboard\Models\Forms\SearchForm $searchForm
 * @var CurrentRoute $currentRoute
 */

?>
<?php
$parameters = iterator_to_array($paginator->read());

if (empty($parameters)) {
    if ($searchForm->isSearch()) {
        echo Bleet::emptyState()
            ->icon('magnifying-glass')
            ->title($translator->translate('No results', category: 'dboard-common'))
            ->primary();
    } else {
        echo Bleet::emptyState()
            ->icon('cog-6-tooth')
            ->title($translator->translate('No parameter', category: 'dboard-modules'))
            ->description($translator->translate('Start by creating a new parameter.', category: 'dboard-modules'))
            ->button($translator->translate('New parameter', category: 'dboard-modules'), $urlGenerator->generate('dboard.parameters.create'))
            ->primary();
    }
} else {
    $dl = Bleet::dl()->cols(4);
    foreach ($parameters as $parameter) {
        /** @var Parameter $parameter */
        $editUrl = $urlGenerator->generate('dboard.parameters.edit', ['domain' => $parameter->getDomain(), 'name' => $parameter->getName()]);
        $deleteUrl = $urlGenerator->generate('dboard.parameters.delete', ['domain' => $parameter->getDomain(), 'name' => $parameter->getName()]);

        $buttonsBar = Bleet::buttonsBar()
            ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-edit')->button())
            ->addButton(Bleet::button()->icon('trash')->danger()->outline()->addAttributes(Bleet::modal()->trigger($deleteUrl))->xs()->attribute('bleet-popover-trigger', 'list-popover-delete'));

        $domainLink = Bleet::a($parameter->getDomain(), $editUrl)->render();
        $valuePreview = $parameter->getValue();
        if ($valuePreview !== null && mb_strlen($valuePreview) > 20) {
            $valuePreview = mb_substr($valuePreview, 0, 20) . '…';
        }

        $dl = $dl
            ->addItem(Bleet::termItem($translator->translate('Domain', category: 'dboard-modules'))->addDetail(Bleet::detailItem($domainLink)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Name', category: 'dboard-modules'))->detail($parameter->getName()))
            ->addItem(Bleet::termItem($translator->translate('Value', category: 'dboard-modules'))->detail($valuePreview ?? '-'))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonsBar->render())->encode(false)));
    }

    echo $dl->primary()->render();
    echo Widgets::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    echo Widgets::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
}
?>


