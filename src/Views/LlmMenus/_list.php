<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dcore\Models\LlmMenu;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\PageForm;
use Blackcube\Dboard\Models\Forms\SearchForm;
use Blackcube\Html\Aurelia;
use Blackcube\Bleet\Bleet;
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
 * @var array<int, LlmMenu> $items
 * @var SearchForm $searchForm
 * @var PageForm $pageForm
 * @var CurrentRoute $currentRoute
 * @var string|null $csrf
 */

?>
<?php
$items = $items ?? iterator_to_array($paginator->read());

if (empty($items) === true):
    if ($searchForm->isSearch() === true):
        echo Bleet::emptyState()
            ->icon('magnifying-glass')
            ->title($translator->translate('No results', category: 'dboard-common'))
            ->primary();
    else:
        echo Bleet::emptyState()
            ->icon('cpu-chip')
            ->title($translator->translate('No LLM menu', category: 'dboard-content'))
            ->description($translator->translate('Start by creating a new LLM menu.', category: 'dboard-content'))
            ->button($translator->translate('New LLM menu', category: 'dboard-content'), $urlGenerator->generate('dboard.llmmenus.create'))
            ->primary();
    endif;
else:
    $treeMoveUrl = $urlGenerator->generate('dboard.llmmenus.move');
    $dl = Bleet::dl()
        ->cols(3)
        ->attribute('dboard-llm-drag-drop', Aurelia::attributesCustomAttribute([
            'url' => $treeMoveUrl,
            'csrf' => $csrf,
            'errorTitle' => $translator->translate('Error', category: 'dboard-common'),
            'errorContent' => $translator->translate('Move failed.', category: 'dboard-common'),
        ]));

    $editPopover = Bleet::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    $deletePopover = Bleet::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');

    foreach ($items as $llmMenu):
        /** @var LlmMenu $llmMenu */
        $editUrl = $urlGenerator->generate('dboard.llmmenus.edit', [
            'id' => $llmMenu->getId(),
        ]);
        $deleteUrl = $urlGenerator->generate('dboard.llmmenus.delete', [
            'id' => $llmMenu->getId(),
        ]);

        $buttonGroup = Bleet::buttonGroup()
            ->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->addAttributes($editPopover->trigger())->button())
            ->addButton(Bleet::button()->icon('trash')->danger()->outline()->addAttributes(Bleet::modal()->trigger($deleteUrl))->xs()->addAttributes($deletePopover->trigger()));

        $handleIcon = Bleet::svg()->solid('bars-3')->addClass('size-4', 'text-secondary-400')->render();
        $handle = Html::tag('span', $handleIcon, [
            'data-llm-drag-drop' => 'handle',
            'class' => [
                'inline-flex',
                'items-center',
                'mr-2',
            ],
        ])->encode(false)->render();

        $menuName = $llmMenu->getName();
        $menuLabel = ($menuName !== null && $menuName !== '' ? $menuName . ' ' : '') . '(#' . $llmMenu->getId() . ')';
        $menuLink = Bleet::a($menuLabel, $editUrl)->render();

        $infoFragments = [];
        $content = $llmMenu->content;
        if ($content !== null):
            $contentIcon = Bleet::svg()->outline('document-text')->addClass('size-4', 'text-secondary-400')->render();
            $contentText = Html::tag('span', Html::encode($content->getName()), [
                'class' => [
                    'text-sm',
                    'text-secondary-600',
                ],
            ])
                ->encode(false)
                ->render();
            $infoFragments[] = $contentIcon . $contentText;
        endif;
        $tag = $llmMenu->tag;
        if ($tag !== null):
            $tagIcon = Bleet::svg()->outline('tag')->addClass('size-4', 'text-secondary-400', count($infoFragments) > 0 ? 'ml-2' : '')->render();
            $tagText = Html::tag('span', Html::encode($tag->getName()), [
                'class' => [
                    'text-sm',
                    'text-secondary-600',
                ],
            ])
                ->encode(false)
                ->render();
            $infoFragments[] = $tagIcon . $tagText;
        endif;

        $menuDetail = $handle . $menuLink;
        if (empty($infoFragments) === false):
            $infoLine = Html::tag('div', implode('', $infoFragments), [
                'class' => [
                    'flex',
                    'items-center',
                    'gap-1',
                    'mt-1',
                ],
            ])
                ->encode(false)
                ->render();
            $menuDetail .= $infoLine;
        endif;

        $dl = $dl
            ->addItem(
                Bleet::termItem($translator->translate('LLM Menu', category: 'dboard-content'))
                    ->level($llmMenu->getLevel())
                    ->rowAttributes([
                        'data-llm-drag-drop' => 'item-' . $llmMenu->getId(),
                        'data-llm-drag-drop-level' => (string) $llmMenu->getLevel(),
                    ])
                    ->addDetail(Bleet::detailItem($menuDetail)->encode(false))
            )
            ->addItem(Bleet::termItem($translator->translate('Description', category: 'dboard-content'))->addDetail(Bleet::detailItem(Html::encode($llmMenu->getDescription() ?? ''))))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonGroup->render())->encode(false)));
    endforeach;

    echo $dl->primary()->render();
    echo $editPopover;
    echo $deletePopover;
endif;
?>
