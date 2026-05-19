<?php

declare(strict_types=1);

/**
 * _list.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Enums\ElasticSchemaKind;
use Blackcube\Dcore\Models\SchemaSchema;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Bleet\Bleet;
use Blackcube\Dboard\Widgets\Widgets;
use Yiisoft\Data\Paginator\OffsetPaginator;
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
$items = iterator_to_array($paginator->read());

if (empty($items)) {
    if ($searchForm->isSearch()) {
        echo Bleet::emptyState()
            ->icon('magnifying-glass')
            ->title($translator->translate('No results', category: 'dboard-common'))
            ->primary();
    } else {
        echo Bleet::emptyState()
            ->icon('arrows-right-left')
            ->title($translator->translate('No mapping', category: 'dboard-modules'))
            ->description($translator->translate('Start by creating a new schema ↔ xeo mapping.', category: 'dboard-modules'))
            ->button($translator->translate('New mapping', category: 'dboard-modules'), $urlGenerator->generate('dboard.xeo.mapping.create'))
            ->primary();
    }
} else {
    $dl = Bleet::dl()->cols(3);
    foreach ($items as $schemaSchema) {
        /** @var SchemaSchema $schemaSchema */
        $regularSchema = $schemaSchema->relation('regularElasticSchema');
        $xeoSchema = $schemaSchema->relation('xeoElasticSchema');
        $regularName = $regularSchema ? $regularSchema->getName() : '?';
        $xeoName = $xeoSchema ? $xeoSchema->getName() : '?';

        $routeParams = [
            'regularElasticSchemaId' => $schemaSchema->getRegularElasticSchemaId(),
            'xeoElasticSchemaId' => $schemaSchema->getXeoElasticSchemaId(),
        ];

        $editUrl = $urlGenerator->generate('dboard.xeo.mapping.edit', $routeParams);
        $deleteUrl = $urlGenerator->generate('dboard.xeo.mapping.delete', $routeParams);

        $deleteButton = Bleet::button()
            ->icon('trash')
            ->danger()
            ->outline()
            ->xs()
            ->addAttributes(Bleet::modal()->trigger($deleteUrl))
            ->attribute('bleet-popover-trigger', 'list-popover-delete');

        $buttonsBar = Bleet::buttonsBar()
            ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-edit')->button())
            ->addButton($deleteButton);

        $linkLabel = Bleet::a($regularName . ' → ' . $xeoName, $editUrl)->render();

        $regularKindBadge = $regularSchema ? match ($regularSchema->getKind()) {
            ElasticSchemaKind::Xeo => Bleet::badge($translator->translate('Xeo', category: 'dboard-modules'))->secondary()->render(),
            ElasticSchemaKind::Page => Bleet::badge($translator->translate('Page', category: 'dboard-modules'))->success()->render(),
            ElasticSchemaKind::Bloc => Bleet::badge($translator->translate('Block', category: 'dboard-modules'))->info()->render(),
            default => Bleet::badge($translator->translate('Common', category: 'dboard-modules'))->secondary()->render(),
        } : '';
        $xeoKindBadge = $xeoSchema ? match ($xeoSchema->getKind()) {
            ElasticSchemaKind::Xeo => Bleet::badge($translator->translate('Xeo', category: 'dboard-modules'))->secondary()->render(),
            ElasticSchemaKind::Page => Bleet::badge($translator->translate('Page', category: 'dboard-modules'))->success()->render(),
            ElasticSchemaKind::Bloc => Bleet::badge($translator->translate('Block', category: 'dboard-modules'))->info()->render(),
            default => Bleet::badge($translator->translate('Common', category: 'dboard-modules'))->secondary()->render(),
        } : '';
        $liaisonBadges = $regularKindBadge . ' → ' . $xeoKindBadge;

        $dl = $dl
            ->addItem(Bleet::termItem($translator->translate('Association', category: 'dboard-modules'))->addDetail(Bleet::detailItem($linkLabel)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Link', category: 'dboard-modules'))->addDetail(Bleet::detailItem($liaisonBadges)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonsBar->render())->encode(false)));
    }

    echo $dl->primary()->render();
    echo Widgets::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    echo Widgets::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
}
?>
