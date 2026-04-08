<?php

declare(strict_types=1);

/**
 * _list.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Enums\ElasticSchemaKind;
use Blackcube\Dcore\Models\Bloc;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Helpers\ElasticFieldRenderer;
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
$elasticSchemas = iterator_to_array($paginator->read());

if (empty($elasticSchemas)) {
    if ($searchForm->isSearch()) {
        echo Bleet::emptyState()
            ->icon('magnifying-glass')
            ->title($translator->translate('No results', category: 'dboard-common'))
            ->primary();
    } else {
        echo Bleet::emptyState()
            ->icon('code-bracket')
            ->title($translator->translate('No schema', category: 'dboard-modules'))
            ->description($translator->translate('Start by creating a new schema.', category: 'dboard-modules'))
            ->button($translator->translate('New schema', category: 'dboard-modules'), $urlGenerator->generate('dboard.elasticschemas.create'))
            ->primary();
    }
} else {
    $dl = Bleet::dl()->cols(4);
    foreach ($elasticSchemas as $elasticSchema) {
        /** @var ElasticSchema $elasticSchema */
        $isUsed = Bloc::query()->andWhere(['elasticSchemaId' => $elasticSchema->getId()])->exists() ||
                  Content::query()->andWhere(['elasticSchemaId' => $elasticSchema->getId()])->exists() ||
                  Tag::query()->andWhere(['elasticSchemaId' => $elasticSchema->getId()])->exists();

        $editUrl = $urlGenerator->generate('dboard.elasticschemas.edit', ['id' => $elasticSchema->getId()]);
        $deleteUrl = $urlGenerator->generate('dboard.elasticschemas.delete', ['id' => $elasticSchema->getId()]);

        $deleteButton = Bleet::button()->icon('trash')->danger()->outline()->xs()
            ->attribute('bleet-popover-trigger', 'list-popover-delete');
        if ($isUsed) {
            $deleteButton = $deleteButton->disabled();
        } else {
            $deleteButton = $deleteButton->addAttributes(Bleet::modal()->trigger($deleteUrl));
        }

        $buttonsBar = Bleet::buttonsBar()
            ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-edit')->button())
            ->addButton($deleteButton);

        $nameLink = Bleet::a($elasticSchema->getName() . ' (#' . $elasticSchema->getId() . ')', $editUrl)->render();

        $infoBadges = '';
        if ($elasticSchema->isBuiltin()) {
            $infoBadges .= Bleet::badge($translator->translate('Builtin', category: 'dboard-modules'))->danger()->render() . ' ';
        }
        if ($elasticSchema->getMdMapping() !== null) {
            $infoBadges .= Bleet::badge($translator->translate('Mapping', category: 'dboard-modules'))->info()->render() . ' ';
        }
        $infoBadges .= match ($elasticSchema->getKind()) {
            ElasticSchemaKind::Xeo => Bleet::badge($translator->translate('Xeo', category: 'dboard-modules'))->secondary()->render(),
            ElasticSchemaKind::Page => Bleet::badge($translator->translate('Page', category: 'dboard-modules'))->success()->render(),
            ElasticSchemaKind::Bloc => Bleet::badge($translator->translate('Block', category: 'dboard-modules'))->info()->render(),
            default => Bleet::badge($translator->translate('Common', category: 'dboard-modules'))->secondary()->render(),
        };

        $viewBadge = ElasticFieldRenderer::getAdminView($elasticSchema) !== false
            ? Bleet::badge($translator->translate('active', category: 'dboard-modules'))->success()->render()
            : Bleet::badge($translator->translate('auto', category: 'dboard-modules'))->secondary()->render();

        $dl = $dl
            ->addItem(Bleet::termItem($translator->translate('Name', category: 'dboard-modules'))->addDetail(Bleet::detailItem($nameLink)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Info', category: 'dboard-modules'))->addDetail(Bleet::detailItem($infoBadges)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('View', category: 'dboard-modules'))->addDetail(Bleet::detailItem($viewBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonsBar->render())->encode(false)));
    }

    echo $dl->primary()->render();
    echo Widgets::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    echo Widgets::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
}
?>
