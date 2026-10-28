<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\LlmMenu;
use Blackcube\Dboard\Enums\TreePosition;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\LlmMenuForm;
use Blackcube\Dboard\Helpers\RouteHelper;
use Blackcube\Html\Aurelia;
use Blackcube\Bleet\Bleet;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
use Yiisoft\Html\Html;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var Administrator $administrator
 * @var LlmMenuForm $formModel
 * @var CurrentRoute $currentRoute
 * @var LlmMenu $llmMenu
 * @var bool $isEdit
 */

$routeHelper = RouteHelper::create();
$contentOptions = $routeHelper->getContentOptions();
$tagOptions = $routeHelper->getTagOptions();
$llmMenuOptions = $routeHelper->getLlmMenuOptions();

$moveModeOptions = [
    TreePosition::Into->value => $translator->translate('Into', category: 'dboard-common'),
    TreePosition::Before->value => $translator->translate('Before', category: 'dboard-common'),
    TreePosition::After->value => $translator->translate('After', category: 'dboard-common'),
];

$showNameSection = true;
$showLinkSection = true;

?>

                <div class="dboard-surface p-6">
                    <?php if ($showNameSection === true): ?>
                    <div class="flex gap-4">
                        <div class="grow">
                            <?php echo Bleet::input($formModel, 'name')
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-4">
                        <div class="grow">
                            <?php echo Bleet::textarea($formModel, 'description')
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($showLinkSection === true): ?>
                    <?php if ($showNameSection === true): ?>
                    <?php echo Bleet::hr($translator->translate('Link', category: 'dboard-content'))->secondary(); ?>
                    <?php endif; ?>

                    <div class="flex gap-4">
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::select($formModel, 'contentId')
                                ->optionsData($contentOptions)
                                ->prompt($translator->translate('-- Select --', category: 'dboard-common'))
                                ->searchable()
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::select($formModel, 'tagId')
                                ->optionsData($tagOptions)
                                ->prompt($translator->translate('-- Select --', category: 'dboard-common'))
                                ->searchable()
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (empty($llmMenuOptions) === false): ?>
                    <?php echo Bleet::hr($translator->translate('Positioning', category: 'dboard-common'))->secondary(); ?>

                    <?php if ($isEdit === true): ?>
                    <?php echo Html::openTag('div', [
                        'class' => 'flex flex-wrap gap-4 p-4 bg-secondary-50 rounded-lg',
                        'dboard-fold' => Aurelia::attributesCustomAttribute([
                            'event' => 'change',
                        ]),
                    ]); ?>
                        <div class="basis-auto" data-fold="trigger">
                            <?php echo Bleet::checkbox($formModel, 'move')
                                ->inputValue('1')
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <?php
                        $targetClasses = [
                            'grow',
                            'flex',
                            'flex-wrap',
                            'gap-4',
                        ];
                        if ($formModel->isMove() === false):
                            $targetClasses[] = 'hidden';
                        endif;
                        echo Html::openTag('div', [
                            'class' => implode(' ', $targetClasses),
                            'data-fold' => 'target',
                        ]);
                        ?>
                    <?php else: ?>
                    <div class="flex flex-wrap gap-4 p-4 bg-secondary-50 rounded-lg">
                        <?php echo Bleet::input($formModel, 'move')->value('1')->hidden()->render(); ?>
                    <?php endif; ?>
                            <div class="basis-1/3 grow">
                                <?php echo Bleet::select($formModel, 'moveMode')
                                    ->optionsData($moveModeOptions)
                                    ->secondary()
                                    ->render();
                                ?>
                            </div>
                            <div class="basis-1/3 grow">
                                <?php echo Bleet::select($formModel, 'moveTargetId')
                                    ->optionsData($llmMenuOptions)
                                    ->prompt($translator->translate('-- Select --', category: 'dboard-common'))
                                    ->secondary()
                                    ->render();
                                ?>
                            </div>
                    <?php if ($isEdit === true): ?>
                        <?php echo Html::closeTag('div'); ?>
                    <?php echo Html::closeTag('div'); ?>
                    <?php else: ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
