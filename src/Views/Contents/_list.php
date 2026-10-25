<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dboard\Components\Rbac;
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
 * @var array<int, Content> $items
 * @var SearchForm $searchForm
 * @var PageForm $pageForm
 * @var CurrentRoute $currentRoute
 * @var int $levelOffset
 * @var string|null $csrf
 * @var callable $userCan
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
            ->icon('document-text')
            ->title($translator->translate('No content', category: 'dboard-content'))
            ->description($translator->translate('Start by creating a new content.', category: 'dboard-content'))
            ->button($translator->translate('New content', category: 'dboard-content'), $urlGenerator->generate('dboard.contents.create'))
            ->primary();
    endif;
else:
    $treeMoveUrl = $urlGenerator->generate('dboard.contents.move');
    $dl = Bleet::dl()
        ->cols(3)
        ->colTemplateClass('md:grid-cols-[3fr_1fr_1fr]')
        ->attribute('dboard-tree-drag-drop', Aurelia::attributesCustomAttribute([
            'url' => $treeMoveUrl,
            'csrf' => $csrf,
            'errorTitle' => $translator->translate('Error', category: 'dboard-common'),
            'errorContent' => $translator->translate('Move failed.', category: 'dboard-common'),
        ]));

    $canUpdate = $userCan(Rbac::PERMISSION_CONTENT_UPDATE);
    $canExport = $userCan(Rbac::PERMISSION_CONTENT_EXPORT);
    $canDelete = $userCan(Rbac::PERMISSION_CONTENT_DELETE);

    $editPopover = Bleet::popover($translator->translate('Edit', category: 'dboard-common'))->id('list-popover-edit');
    $deactivatePopover = Bleet::popover($translator->translate('Deactivate', category: 'dboard-common'))->id('list-popover-deactivate');
    $activatePopover = Bleet::popover($translator->translate('Activate', category: 'dboard-common'))->id('list-popover-activate');
    $exportPopover = Bleet::popover($translator->translate('Export', category: 'dboard-common'))->id('list-popover-export');
    $deletePopover = Bleet::popover($translator->translate('Delete', category: 'dboard-common'))->id('list-popover-delete');
    $publicUrlPopover = Bleet::popover($translator->translate('View on site', category: 'dboard-common'))->id('list-popover-public-url');

    foreach ($items as $content):
        /** @var Content $content */
        $editUrl = $urlGenerator->generate('dboard.contents.edit', [
            'id' => $content->getId(),
        ]);

        $slug = $content->getSlugId() !== null ? $content->slug : null;
        if ($slug !== null):
            $host = $slug->host;
            $hostName = $host !== null ? $host->getName() : 'localhost';
            $urlLabel = 'https://' . $hostName . '/' . $slug->getPath();
        else:
            $hostName = null;
            $urlLabel = $translator->translate('Not routable', category: 'dboard-common');
        endif;

        $buttonGroup = Bleet::buttonGroup();

        if ($canUpdate === true):
            $buttonGroup = $buttonGroup->addButton(Bleet::a()->url($editUrl)->icon('pencil')->info()->outline()->xs()->addAttributes($editPopover->trigger())->button());

            $toggleUrl = $urlGenerator->generate('dboard.contents.toggle', [
                'id' => $content->getId(),
            ]);
            $toggleButton = $content->isActive() === true
                ? Bleet::button()->icon('pause')->warning()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()->addAttributes($deactivatePopover->trigger())
                : Bleet::button()->icon('play')->success()->outline()->addAttributes(Bleet::modal()->trigger($toggleUrl))->xs()->addAttributes($activatePopover->trigger());
            $buttonGroup = $buttonGroup->addButton($toggleButton);
        endif;

        if ($canExport === true):
            $exportUrl = $urlGenerator->generate('dboard.contents.export', [
                'id' => $content->getId(),
            ]);
            $buttonGroup = $buttonGroup->addButton(Bleet::a()->url($exportUrl)->icon('arrow-down-tray')->secondary()->outline()->xs()->attribute('download', '')->addAttributes($exportPopover->trigger())->button());
        endif;

        if ($canDelete === true):
            $deleteUrl = $urlGenerator->generate('dboard.contents.delete', [
                'id' => $content->getId(),
            ]);
            $buttonGroup = $buttonGroup->addButton(Bleet::button()->icon('trash')->danger()->outline()->addAttributes(Bleet::modal()->trigger($deleteUrl))->xs()->addAttributes($deletePopover->trigger()));
        endif;

        if ($slug !== null && $hostName !== null):
            $publicUrl = $hostName === '*'
                ? '/' . $slug->getPath()
                : 'https://' . $hostName . '/' . $slug->getPath();
            $buttonGroup = $buttonGroup->addButton(Bleet::a()->url($publicUrl)->icon('globe-alt')->success()->outline()->xs()->attribute('target', '_blank')->addAttributes($publicUrlPopover->trigger())->button());
        endif;

        $handleIcon = Bleet::svg()->solid('bars-3')->addClass('size-4', 'text-secondary-400')->render();
        $handle = Html::tag('span', $handleIcon, [
            'data-tree-drag-drop' => 'handle',
            'class' => [
                'inline-flex',
                'items-center',
                'mr-2',
            ],
        ])->encode(false)->render();

        $contentLink = Bleet::a($content->getName() . ' (#' . $content->getId() . ')', $editUrl)->render();

        if ($content->getTypeId() !== null):
            $type = $content->type;
            $typeLabel = $type !== null ? $type->getName() . ' (#' . $type->getId() . ')' : 'Type #' . $content->getTypeId();
        else:
            $typeLabel = '';
        endif;

        $languageIcon = Bleet::svg()->outline('language')->addClass('size-4', 'text-secondary-400')->render();
        $languageText = Html::tag('span', Html::encode($content->getLanguageId()), [
            'class' => [
                'text-sm',
                'text-secondary-600',
            ],
        ])
            ->encode(false)
            ->render();

        $urlIcon = Bleet::svg()->outline('link')->addClass('size-4', 'text-secondary-400', 'ml-2')->render();
        $urlText = Html::tag('span', Html::encode($urlLabel), [
            'class' => [
                'text-sm',
                'text-secondary-600',
            ],
        ])
            ->encode(false)
            ->render();

        $urlLine = Html::tag('div', $languageIcon . $languageText . $urlIcon . $urlText, [
            'class' => [
                'flex',
                'items-center',
                'gap-1',
                'mt-1',
                'ml-2',
            ],
        ])
            ->encode(false)
            ->render();

        $typeLine = '';
        if ($typeLabel !== ''):
            $typeIcon = Bleet::svg()->outline('cube')->addClass('size-4', 'text-secondary-400')->render();
            $typeText = Html::tag('span', Html::encode($typeLabel), [
                'class' => [
                    'text-sm',
                    'text-secondary-600',
                ],
            ])
                ->encode(false)
                ->render();
            $typeLine = Html::tag('div', $typeIcon . $typeText, [
                'class' => [
                    'flex',
                    'items-center',
                    'gap-1',
                    'mt-1',
                    'ml-2',
                ],
            ])
                ->encode(false)
                ->render();
        endif;

        $contentDetail = $handle . $contentLink . $urlLine . $typeLine;

        $statusBadge = $content->isActive() === true
            ? Bleet::badge($translator->translate('Active', category: 'dboard-common'))->success()->render()
            : Bleet::badge($translator->translate('Inactive', category: 'dboard-common'))->danger()->render();

        $dl = $dl
            ->addItem(
                Bleet::termItem($translator->translate('Content', category: 'dboard-content'))
                    ->level($content->getLevel() - $levelOffset)
                    ->rowAttributes([
                        'data-tree-drag-drop' => 'item-' . $content->getId(),
                    ])
                    ->addDetail(Bleet::detailItem($contentDetail)->encode(false))
            )
            ->addItem(Bleet::termItem($translator->translate('Status', category: 'dboard-common'))->addDetail(Bleet::detailItem($statusBadge)->encode(false)))
            ->addItem(Bleet::termItem($translator->translate('Actions', category: 'dboard-common'))->addDetail(Bleet::detailItem($buttonGroup->render())->encode(false)));
    endforeach;

    echo $dl->primary()->render();
    echo $editPopover;
    echo $deactivatePopover;
    echo $activatePopover;
    echo $exportPopover;
    echo $deletePopover;
    echo $publicUrlPopover;
endif;
?>
