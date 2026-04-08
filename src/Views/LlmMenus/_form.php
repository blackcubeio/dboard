<?php

declare(strict_types=1);

/**
 * _form.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\LlmMenu;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\LlmMenuForm;
use Blackcube\Dboard\Helpers\RouteHelper;
use Blackcube\Bleet\Aurelia;
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
$contentOptions = $routeHelper->getContentRoutes(byId: true);
$tagOptions = $routeHelper->getTagRoutes(byId: true);
$llmMenuOptions = $routeHelper->getLlmMenuRoutes(byId: true);

// Move mode options
$moveModeOptions = [
    'into' => $translator->translate('Into', category: 'dboard-common'),
    'before' => $translator->translate('Before', category: 'dboard-common'),
    'after' => $translator->translate('After', category: 'dboard-common'),
];

// Edit mode: level determines which fields are shown
// Level 1-2: name + description | Level 3: contentId + tagId
// Create mode: show all fields (handler selects what's relevant)
$editLevel = $isEdit ? $llmMenu->getLevel() : 0;
$showNameSection = !$isEdit || $editLevel < 3;
$showLinkSection = (!$isEdit && !empty($llmMenuOptions)) || ($isEdit && $editLevel >= 3);

?>

                <div class="bg-white rounded-b-lg shadow-lg p-6">
                    <?php if ($showNameSection): ?>
                    <div class="flex gap-4">
                        <div class="grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'name')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::input()
                                    ->active($formModel, 'name')
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-4">
                        <div class="grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'description')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::textarea()
                                    ->active($formModel, 'description')
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($showLinkSection): ?>
                    <?php if ($showNameSection): ?>
                    <?php echo Bleet::hr($translator->translate('Link', category: 'dboard-content'))->secondary(); ?>
                    <?php endif; ?>

                    <div class="flex gap-4">
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'contentId')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::select()
                                    ->active($formModel, 'contentId')
                                    ->options($contentOptions)
                                    ->placeholder($translator->translate('-- Select --', category: 'dboard-common'))
                                    ->searchable()
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'tagId')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::select()
                                    ->active($formModel, 'tagId')
                                    ->options($tagOptions)
                                    ->placeholder($translator->translate('-- Select --', category: 'dboard-common'))
                                    ->searchable()
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($llmMenuOptions)): ?>
                    <?php echo Bleet::hr($translator->translate('Positioning', category: 'dboard-common'))->secondary(); ?>

                    <?php if ($isEdit): ?>
                    <?php echo Html::openTag('div', [
                        'class' => 'flex flex-wrap gap-4 p-4 bg-secondary-50 rounded-lg',
                        'dboard-fold' => Aurelia::attributesCustomAttribute(['event' => 'change']),
                    ]); ?>
                        <div class="basis-auto" data-fold="trigger">
                            <?php echo Bleet::checkbox()
                                ->active($formModel, 'move')
                                ->value('1')
                                ->primary()
                                ->render();
                            ?>
                        </div>
                        <?php
                        $targetClasses = ['grow', 'flex', 'flex-wrap', 'gap-4'];
                        if (!$formModel->isMove()) {
                            $targetClasses[] = 'hidden';
                        }
                        echo Html::openTag('div', ['class' => implode(' ', $targetClasses), 'data-fold' => 'target']);
                        ?>
                    <?php else: ?>
                    <div class="flex flex-wrap gap-4 p-4 bg-secondary-50 rounded-lg">
                        <?php echo Bleet::input()->active($formModel, 'move')->value('1')->hidden()->render(); ?>
                    <?php endif; ?>
                            <div class="basis-1/3 grow">
                                <?php echo Bleet::label()
                                    ->active($formModel, 'moveMode')
                                    ->primary()
                                    ->render();
                                ?>
                                <div class="mt-2">
                                    <?php echo Bleet::select()
                                        ->active($formModel, 'moveMode')
                                        ->options($moveModeOptions)
                                        ->primary()
                                        ->render();
                                    ?>
                                </div>
                            </div>
                            <div class="basis-1/3 grow">
                                <?php echo Bleet::label()
                                    ->active($formModel, 'moveTargetId')
                                    ->primary()
                                    ->render();
                                ?>
                                <div class="mt-2">
                                    <?php echo Bleet::select()
                                        ->active($formModel, 'moveTargetId')
                                        ->options($llmMenuOptions)
                                        ->placeholder($translator->translate('-- Select --', category: 'dboard-common'))
                                        ->primary()
                                        ->render();
                                    ?>
                                </div>
                            </div>
                    <?php if ($isEdit): ?>
                        <?php echo Html::closeTag('div'); ?>
                    <?php echo Html::closeTag('div'); ?>
                    <?php else: ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
