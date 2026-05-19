<?php

declare(strict_types=1);

/**
 * _list_flat.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dboard\Components\Rbac;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\PageForm;
use Blackcube\Dboard\Models\Forms\SearchForm;
use Blackcube\Bleet\Bleet;
use Blackcube\Dboard\Widgets\Widgets;
use Yiisoft\Html\Html;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var Administrator $administrator
 * @var ActiveQueryPaginator $paginator
 * @var SearchForm $searchForm
 * @var PageForm $pageForm
 * @var CurrentRoute $currentRoute
 * @var string|null $csrf
 * @var callable $userCan
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
            ->icon('document-text')
            ->title($translator->translate('No content', category: 'dboard-content'))
            ->description($translator->translate('Start by creating a new content.', category: 'dboard-content'))
            ->button($translator->translate('New content', category: 'dboard-content'), $urlGenerator->generate('dboard.contents.create'))
            ->primary();
    }
} else {
    $dl = Bleet::dl()
        ->cols(4)
        ->colTemplateClass('md:grid-cols-[3fr_1fr_1fr_1fr]');

    foreach ($items as $content) {
        /** @var Content $content */
        $editUrl = $urlGenerator->generate('dboard.contents.edit', ['id' => $content->getId()]);

        $buttonsBar = Bleet::buttonsBar();

        // Edit button
        if ($userCan(Rbac::PERMISSION_CONTENT_UPDATE)) {
            $buttonsBar = $buttonsBar->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->attribute('bleet-popover-trigger', 'list-popover-edit')->button());

            // Toggle button
            $toggleUrl = $urlGenerator->generate('dboard.contents.toggle', ['id' => $content->getId()]);
            $toggleButton = $content->isActive()
                ? Bleet::button()->icon('pause')->warning()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()->attribute('bleet-popover-trigger', 'list-popover-deactivate')
                : Bleet::button()->icon('play')->success()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()->attribute('bleet-popover-trigger', 'list-popover-activate');
            $buttonsBar = $buttonsBar->addButton($toggleButton);
        }

        // Export button
        if ($userCan(Rbac::PERMISSION_CONTENT_EXPORT)) {
            $exportUrl = $urlGenerator->generate('dboard.contents.export', ['id' => $content->getId()]);
            $buttonsBar = $buttonsBar->addButton(Bleet::a()->url($exportUrl)->icon('arrow-down-tray')->secondary()->outline()->xs()->attribute('download', '')->attribute('bleet-popover-trigger', 'list-popover-export')->button());
        }

        // Delete button
        if ($userCan(Rbac::PERMISSION_CONTENT_DELETE)) {
            $deleteUrl = $urlGenerator->generate('dboard.contents.delete', ['id' => $content->getId()]);
            $buttonsBar = $buttonsBar->addButton(Bleet::button()->icon('trash')->danger()->outline()->addAttributes(Bleet::modal()->trigger($deleteUrl))->xs()->attribute('bleet-popover-trigger', 'list-popover-delete'));
        }

        // Fetch slug + host (used for URL display and public URL button)
        $slug = $content->getSlugId() !== null ? $content->getSlugQuery()->one() : null;
        if ($slug !== null) {
            $host = $slug->getHostQuery()->one();
            $hostName = $host !== null ? $host->getName() : 'localhost';
            $urlLabel = 'https://' . $hostName . '/' . $slug->getPath();
        } else {
            $hostName = null;
            $urlLabel = $translator->translate('Not routable', category: 'dboard-common');
        }

        // Public URL button (enabled if slug exists)
        if ($slug !== null && $hostName !== null) {
            $publicUrl = $hostName === '*'
                ? '/' . $slug->getPath()
                : 'https://' . $hostName . '/' . $slug->getPath();
            $buttonsBar = $buttonsBar->addButton(Bleet::a()->url($publicUrl)->icon('globe-alt')->success()->outline()->xs()->attribute('target', '_blank')->attribute('bleet-popover-trigger', 'list-popover-public-url')->button());
        }

        $contentLink = Bleet::a($content->getName() . ' (#' . $content->getId() . ')', $editUrl)->render();

        if ($content->getTypeId() !== null) {
            $type = $content->getTypeQuery()->one();
            $typeLabel = $type !== null ? $type->getName() . ' (#' . $type->getId() . ')' : 'Type #' . $content->getTypeId();
        } else {
            $typeLabel = '';
        }

        $languageIcon = Bleet::svg()->outline('language')->addClass('size-4', 'text-secondary-400')->render();
        $languageText = Html::tag('span', Html::encode($content->getLanguageId()), ['class' => ['text-sm', 'text-secondary-600']])
            ->encode(false)
            ->render();

        $urlIcon = Bleet::svg()->outline('link')->addClass('size-4', 'text-secondary-400', 'ml-2')->render();
        $urlText = Html::tag('span', Html::encode($urlLabel), ['class' => ['text-sm', 'text-secondary-600']])
            ->encode(false)
            ->render();

        $urlLine = Html::tag('div', $languageIcon . $languageText . $urlIcon . $urlText, ['class' => ['flex', 'items-center', 'gap-1', 'mt-1']])
            ->encode(false)
            ->render();

        $typeLine = '';
        if ($typeLabel !== '') {
            $typeIcon = Bleet::svg()->outline('cube')->addClass('size-4', 'text-secondary-400')->render();
            $typeText = Html::tag('span', Html::encode($typeLabel), ['class' => ['text-sm', 'text-secondary-600']])
                ->encode(false)
                ->render();
            $typeLine = Html::tag('div', $typeIcon . $typeText, ['class' => ['flex', 'items-center', 'gap-1', 'mt-1']])
                ->encode(false)
                ->render();
        }

        $contentDetail = $contentLink . $urlLine . $typeLine;

        $statusBadge = $content->isActive()
            ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
            : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

        $dateUpdate = $content->getDateUpdate();
        $dateLabel = $dateUpdate !== null
            ? Html::encode((new \IntlDateFormatter($translator->getLocale(), \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT))->format($dateUpdate))
            : '';

        $dl = $dl
            ->addItem(
                Bleet::termItem($translator->translate('Content', category: 'dboard-content'))
                    ->addDetail(Bleet::detailItem($contentDetail)->encode(false))
            )
            ->addItem(Bleet::termItem($translator->translate('Updated', category: 'dboard-common'))->addDetail(Bleet::detailItem($dateLabel)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonsBar->render())->encode(false)));
    }

    echo $dl->primary()->render();
    echo Widgets::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    echo Widgets::popover($translator->translate('Deactivate', category: 'dboard-common'))->id('list-popover-deactivate');
    echo Widgets::popover($translator->translate('Activate', category: 'dboard-common'))->id('list-popover-activate');
    echo Widgets::popover($translator->translate('Export', category: 'dboard-common'))->id('list-popover-export');
    echo Widgets::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
    echo Widgets::popover($translator->translate('View on site', category: 'dboard-common'))->id('list-popover-public-url');
}
?>
