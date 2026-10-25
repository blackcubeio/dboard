<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Content;
use Blackcube\Dboard\Enums\TreePosition;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\ContentForm;
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
 * @var ContentForm $formModel
 * @var CurrentRoute $currentRoute
 * @var Content $content
 * @var ActiveQueryInterface $languageQuery
 * @var ActiveQueryInterface $typeQuery
 * @var array<int, string> $elasticSchemaOptions
 * @var bool $isEdit
 */

$targetOptions = RouteHelper::create()->getContentOptions();
$languageOptions = [];
/** @var Language $language */
foreach ($languageQuery->each() as $language):
    $languageOptions[$language->getId()] = $language->getName();
endforeach;

$typeOptions = [];
/** @var Type $type */
foreach ($typeQuery->each() as $type):
    $typeOptions[$type->getId()] = $type->getName();
endforeach;

$moveModeOptions = [
    TreePosition::Into->value => $translator->translate('Into', category: 'dboard-common'),
    TreePosition::Before->value => $translator->translate('Before', category: 'dboard-common'),
    TreePosition::After->value => $translator->translate('After', category: 'dboard-common'),
];

?>

                <div class="bg-white rounded-b-lg shadow-lg p-6">
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
                                <?php echo Bleet::select($formModel, 'typeId')
                                    ->optionsData($typeOptions)
                                    ->prompt($translator->translate('-- None --', category: 'dboard-common'))
                                    ->secondary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('Properties', category: 'dboard-content'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-4/6 flex gap-4">
                            <div class="basis-1/2">
                                <?php echo Bleet::input($formModel, 'dateStart')
                                    ->date()
                                    ->secondary()
                                    ->render();
                                ?>
                            </div>
                            <div class="basis-1/2">
                                <?php echo Bleet::input($formModel, 'dateEnd')
                                        ->date()
                                        ->secondary()
                                        ->render();
                                ?>
                            </div>
                        </div>
                        <div class="basis-2/6 flex gap-4">
                            <div class="basis-full">
                                <?php echo Bleet::select($formModel, 'elasticSchemaId')
                                        ->optionsData($elasticSchemaOptions)
                                        ->prompt($translator->translate('-- None --', category: 'dboard-common'))
                                        ->secondary()
                                        ->render();
                                ?>
                            </div>
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
