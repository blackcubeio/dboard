<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Language;
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
 * @var array<int, Language> $items
 * @var array<string, true> $usedLanguageIds
 * @var \Blackcube\Dboard\Models\Forms\SearchForm $searchForm
 * @var CurrentRoute $currentRoute
 */

?>
<?php
$languages = $items ?? iterator_to_array($paginator->read());

if (empty($languages) === true):
    if ($searchForm->isSearch() === true):
        echo Bleet::emptyState()
            ->icon('magnifying-glass')
            ->title($translator->translate('No results', category: 'dboard-common'))
            ->primary();
    else:
        echo Bleet::emptyState()
            ->icon('flag')
            ->title($translator->translate('No language', category: 'dboard-modules'))
            ->description($translator->translate('Start by creating a new language.', category: 'dboard-modules'))
            ->button($translator->translate('New language', category: 'dboard-modules'), $urlGenerator->generate('dboard.languages.create'))
            ->primary();
    endif;
else:
    $mainLanguageNames = [];
    foreach ($languages as $lang):
        /** @var Language $lang */
        if ($lang->isMain() === true):
            $mainLanguageNames[$lang->getId()] = $lang->getName();
        endif;
    endforeach;

    $dl = Bleet::dl()->cols(4);
    $editPopover = Bleet::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    $deletePopover = Bleet::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
    $deactivatePopover = Bleet::popover($translator->translate('Deactivate', category: 'dboard-common'))->id('list-popover-deactivate');
    $activatePopover = Bleet::popover($translator->translate('Activate', category: 'dboard-common'))->id('list-popover-activate');
    foreach ($languages as $language):
        /** @var Language $language */
        $parentName = null;
        if ($language->isMain() === false && str_contains($language->getId(), '-') === true):
            $parentId = explode('-', $language->getId())[0];
            $parentName = $mainLanguageNames[$parentId] ?? $parentId;
        endif;

        $editUrl = $urlGenerator->generate('dboard.languages.edit', [
            'id' => $language->getId(),
        ]);
        $toggleUrl = $urlGenerator->generate('dboard.languages.toggle', [
            'id' => $language->getId(),
        ]);
        $deleteUrl = $urlGenerator->generate('dboard.languages.delete', [
            'id' => $language->getId(),
        ]);

        $isUsed = isset($usedLanguageIds[$language->getId()]) === true;

        $toggleButton = $language->isActive() === true
            ? Bleet::button()->icon('pause')->warning()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()
                ->addAttributes($deactivatePopover->trigger())
            : Bleet::button()->icon('play')->success()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()
                ->addAttributes($activatePopover->trigger());

        $deleteButton = Bleet::button()->icon('trash')->danger()->outline()->xs()
            ->addAttributes($deletePopover->trigger());
        if ($isUsed === true):
            $deleteButton = $deleteButton->disabled();
        else:
            $deleteButton = $deleteButton->addAttributes(Bleet::modal()->trigger($deleteUrl));
        endif;

        $buttonGroup = Bleet::buttonGroup()
            ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->addAttributes($editPopover->trigger())->button())
            ->addButton($deleteButton)
            ->addButton($toggleButton);

        $languageLink = Bleet::a($language->getName() . ' (#' . $language->getId() . ')', $editUrl)->render();

        $typeLabel = $language->isMain() === true ? $translator->translate('Primary', category: 'dboard-modules') : ($parentName ?? $translator->translate('Variant', category: 'dboard-modules'));

        $statusBadge = $language->isActive() === true
            ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
            : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

        $dl = $dl
            ->addItem(Bleet::termItem($translator->translate('Language', category: 'dboard-modules'))->addDetail(Bleet::detailItem($languageLink)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Type', category: 'dboard-modules'))->detail($typeLabel))
            ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonGroup->render())->encode(false)));
    endforeach;

    echo $dl->primary()->render();
    echo $editPopover;
    echo $deletePopover;
    echo $deactivatePopover;
    echo $activatePopover;
endif;
?>



