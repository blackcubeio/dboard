<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Parameter;
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
 * @var \Blackcube\Dboard\Models\Forms\SearchForm $searchForm
 * @var CurrentRoute $currentRoute
 */

?>
<?php
$parameters = iterator_to_array($paginator->read());

if (empty($parameters) === true):
    if ($searchForm->isSearch() === true):
        echo Bleet::emptyState()
            ->icon('magnifying-glass')
            ->title($translator->translate('No results', category: 'dboard-common'))
            ->primary();
    else:
        echo Bleet::emptyState()
            ->icon('cog-6-tooth')
            ->title($translator->translate('No parameter', category: 'dboard-modules'))
            ->description($translator->translate('Start by creating a new parameter.', category: 'dboard-modules'))
            ->button($translator->translate('New parameter', category: 'dboard-modules'), $urlGenerator->generate('dboard.parameters.create'))
            ->primary();
    endif;
else:
    $dl = Bleet::dl()->cols(4);
    $editPopover = Bleet::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    $deletePopover = Bleet::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
    foreach ($parameters as $parameter):
        /** @var Parameter $parameter */
        $editUrl = $urlGenerator->generate('dboard.parameters.edit', [
            'domain' => $parameter->getDomain(),
            'name' => $parameter->getName(),
        ]);
        $deleteUrl = $urlGenerator->generate('dboard.parameters.delete', [
            'domain' => $parameter->getDomain(),
            'name' => $parameter->getName(),
        ]);

        $buttonGroup = Bleet::buttonGroup()
            ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->addAttributes($editPopover->trigger())->button())
            ->addButton(Bleet::button()->icon('trash')->danger()->outline()->addAttributes(Bleet::modal()->trigger($deleteUrl))->xs()->addAttributes($deletePopover->trigger()));

        $domainLink = Bleet::a($parameter->getDomain(), $editUrl)->render();
        $valuePreview = $parameter->getValue();
        if ($valuePreview !== null && mb_strlen($valuePreview) > 20):
            $valuePreview = mb_substr($valuePreview, 0, 20) . '…';
        endif;

        $dl = $dl
            ->addItem(Bleet::termItem($translator->translate('Domain', category: 'dboard-modules'))->addDetail(Bleet::detailItem($domainLink)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Name', category: 'dboard-modules'))->detail($parameter->getName()))
            ->addItem(Bleet::termItem($translator->translate('Value', category: 'dboard-modules'))->detail($valuePreview ?? '-'))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonGroup->render())->encode(false)));
    endforeach;

    echo $dl->primary()->render();
    echo $editPopover;
    echo $deletePopover;
endif;
?>


