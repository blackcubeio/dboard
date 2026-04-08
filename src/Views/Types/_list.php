<?php

declare(strict_types=1);

/**
 * _list.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dcore\Models\Type;
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
$types = iterator_to_array($paginator->read());

if (empty($types)) {
    if ($searchForm->isSearch()) {
        echo Bleet::emptyState()
            ->icon('magnifying-glass')
            ->title($translator->translate('No results', category: 'dboard-common'))
            ->primary();
    } else {
        echo Bleet::emptyState()
            ->icon('rectangle-stack')
            ->title($translator->translate('No type', category: 'dboard-modules'))
            ->description($translator->translate('Start by creating a new type.', category: 'dboard-modules'))
            ->button($translator->translate('New type', category: 'dboard-modules'), $urlGenerator->generate('dboard.types.create'))
            ->primary();
    }
} else {
    $dl = Bleet::dl()->cols(5);
    foreach ($types as $type) {
        /** @var Type $type */
        $isUsed = Content::query()->andWhere(['typeId' => $type->getId()])->exists() ||
                  Tag::query()->andWhere(['typeId' => $type->getId()])->exists();

        $editUrl = $urlGenerator->generate('dboard.types.edit', ['id' => $type->getId()]);
        $deleteUrl = $urlGenerator->generate('dboard.types.delete', ['id' => $type->getId()]);

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

        $nameLink = Bleet::a($type->getName() . ' (#' . $type->getId() . ')', $editUrl)->render();

        $contentBadge = $type->isContentAllowed()
            ? Bleet::badge($translator->translate('Yes', category: 'dboard-common'))->success()->render()
            : Bleet::badge($translator->translate('No', category: 'dboard-common'))->danger()->render();

        $tagBadge = $type->isTagAllowed()
            ? Bleet::badge($translator->translate('Yes', category: 'dboard-common'))->success()->render()
            : Bleet::badge($translator->translate('No', category: 'dboard-common'))->danger()->render();

        $dl = $dl
            ->addItem(Bleet::termItem($translator->translate('Name', category: 'dboard-modules'))->addDetail(Bleet::detailItem($nameLink)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Handler', category: 'dboard-modules'))->detail($type->getHandler() ?? '-'))
            ->addItem(Bleet::termItem($translator->translate('Content', category: 'dboard-modules'))->addDetail(Bleet::detailItem($contentBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Tag', category: 'dboard-modules'))->addDetail(Bleet::detailItem($tagBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonsBar->render())->encode(false)));
    }

    echo $dl->primary()->render();
    echo Widgets::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    echo Widgets::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
}
?>
