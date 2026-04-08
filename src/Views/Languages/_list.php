<?php

declare(strict_types=1);

/**
 * index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Models\Menu;
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
$languages = iterator_to_array($paginator->read());

if (empty($languages)) {
    if ($searchForm->isSearch()) {
        echo Bleet::emptyState()
            ->icon('magnifying-glass')
            ->title($translator->translate('No results', category: 'dboard-common'))
            ->primary();
    } else {
        echo Bleet::emptyState()
            ->icon('flag')
            ->title($translator->translate('No language', category: 'dboard-modules'))
            ->description($translator->translate('Start by creating a new language.', category: 'dboard-modules'))
            ->button($translator->translate('New language', category: 'dboard-modules'), $urlGenerator->generate('dboard.languages.create'))
            ->primary();
    }
} else {
    $languagesByMain = [];
    foreach ($languages as $lang) {
        /** @var Language $lang */
        if ($lang->isMain()) {
            $languagesByMain[$lang->getId()] = $lang->getName();
        }
    }

    $dl = Bleet::dl()->cols(4);
    foreach ($languages as $language) {
        /** @var Language $language */
        $parentName = null;
        if (!$language->isMain() && str_contains($language->getId(), '-')) {
            $parentId = explode('-', $language->getId())[0];
            $parentName = $languagesByMain[$parentId] ?? $parentId;
        }

        $editUrl = $urlGenerator->generate('dboard.languages.edit', ['id' => $language->getId()]);
        $toggleUrl = $urlGenerator->generate('dboard.languages.toggle', ['id' => $language->getId()]);
        $deleteUrl = $urlGenerator->generate('dboard.languages.delete', ['id' => $language->getId()]);

        $isUsed = Content::query()->andWhere(['languageId' => $language->getId()])->exists()
            || Menu::query()->andWhere(['languageId' => $language->getId()])->exists();

        $toggleButton = $language->isActive()
            ? Bleet::button()->icon('pause')->warning()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()
                ->attribute('bleet-popover-trigger', 'list-popover-deactivate')
            : Bleet::button()->icon('play')->success()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()
                ->attribute('bleet-popover-trigger', 'list-popover-activate');

        $deleteButton = Bleet::button()->icon('trash')->danger()->outline()->xs()
            ->attribute('bleet-popover-trigger', 'list-popover-delete');
        if ($isUsed) {
            $deleteButton = $deleteButton->disabled();
        } else {
            $deleteButton = $deleteButton->addAttributes(Bleet::modal()->trigger($deleteUrl));
        }

        $buttonsBar = Bleet::buttonsBar()
            ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-edit')->button())
            ->addButton($deleteButton)
            ->addButton($toggleButton);

        $languageLink = Bleet::a($language->getName() . ' (#' . $language->getId() . ')', $editUrl)->render();

        $typeLabel = $language->isMain() ? $translator->translate('Primary', category: 'dboard-modules') : ($parentName ?? $translator->translate('Variant', category: 'dboard-modules'));

        $statusBadge = $language->isActive()
            ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
            : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

        $dl = $dl
            ->addItem(Bleet::termItem($translator->translate('Language', category: 'dboard-modules'))->addDetail(Bleet::detailItem($languageLink)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Type', category: 'dboard-modules'))->detail($typeLabel))
            ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonsBar->render())->encode(false)));
    }

    echo $dl->primary()->render();
    echo Widgets::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    echo Widgets::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
    echo Widgets::popover($translator->translate('Deactivate', category: 'dboard-common'))->id('list-popover-deactivate');
    echo Widgets::popover($translator->translate('Activate', category: 'dboard-common'))->id('list-popover-activate');
}
?>



