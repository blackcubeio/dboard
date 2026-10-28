<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Menu;
use Blackcube\Dboard\Enums\TreePosition;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\MenuForm;
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
 * @var MenuForm $formModel
 * @var CurrentRoute $currentRoute
 * @var Menu $menu
 * @var ActiveQueryInterface $languageQuery
 * @var ActiveQueryInterface $hostQuery
 * @var bool $isEdit
 */

$routeHelper = RouteHelper::create();
$routeOptions = $routeHelper->getRoutes();
$targetOptions = $routeHelper->getMenuOptions();
$languageOptions = [];
/** @var Lang $lang */
foreach ($languageQuery->each() as $lang):
    $languageOptions[$lang->getId()] = $lang->getName();
endforeach;

$hostOptions = [];
/** @var Host $host */
foreach ($hostQuery->each() as $host):
    $hostOptions[$host->getId()] = $host->getName();
endforeach;

$moveModeOptions = [
    TreePosition::Into->value => $translator->translate('Into', category: 'dboard-common'),
    TreePosition::Before->value => $translator->translate('Before', category: 'dboard-common'),
    TreePosition::After->value => $translator->translate('After', category: 'dboard-common'),
];

?>

                <div class="dboard-surface p-6">
                    <div class="basis-full mb-4">
                        <?php echo Bleet::toggle($formModel, 'active')
                            ->secondary()
                            ->render();
                        ?>
                    </div>

                    <div class="flex gap-4">
                        <div class="basis-4/6 flex gap-4">
                            <div class="grow">
                                <?php echo Bleet::input($formModel, 'name')
                                    ->secondary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                        <div class="basis-2/6 flex gap-4">
                            <div class="basis-1/2">
                                <?php echo Bleet::select($formModel, 'languageId')
                                    ->optionsData($languageOptions)
                                    ->prompt($translator->translate('-- Select --', category: 'dboard-common'))
                                    ->secondary()
                                    ->render();
                                ?>
                            </div>
                            <div class="basis-1/2">
                                <?php echo Bleet::select($formModel, 'hostId')
                                    ->optionsData($hostOptions)
                                    ->secondary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('Link', category: 'dboard-content'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::select($formModel, 'route')
                                ->optionsData($routeOptions)
                                ->prompt($translator->translate('-- Select a route --', category: 'dboard-common'))
                                ->searchable()
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::input($formModel, 'queryString')
                                ->secondary()
                                ->render();
                        ?>
                        </div>
                    </div>

                    <?php if (empty($targetOptions) === false): ?>
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
                                    ->optionsData($targetOptions)
                                    ->prompt($isEdit === true ? $translator->translate('-- Select --', category: 'dboard-common') : $translator->translate('-- Root --', category: 'dboard-common'))
                                    ->searchable()
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
