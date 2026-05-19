<?php

declare(strict_types=1);

/**
 * _form.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\TagForm;
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
 * @var TagForm $formModel
 * @var CurrentRoute $currentRoute
 * @var Tag $tag
 * @var ActiveQueryInterface $languageQuery
 * @var ActiveQueryInterface $typeQuery
 * @var array<int, string> $elasticSchemaOptions
 * @var bool $isEdit
 */

$targetOptions = RouteHelper::create()->getTagRoutes(byId: true);
// Prepare language options
$languageOptions = [];
foreach ($languageQuery->each() as $language) {
    $languageOptions[$language->getId()] = $language->getName();
}

// Prepare type options
$typeOptions = [];
foreach ($typeQuery->each() as $type) {
    $typeOptions[$type->getId()] = $type->getName();
}

// Move mode options
$moveModeOptions = [
    'into' => $translator->translate('Into', category: 'dboard-common'),
    'before' => $translator->translate('Before', category: 'dboard-common'),
    'after' => $translator->translate('After', category: 'dboard-common'),
];

?>

                <div class="bg-white rounded-b-lg shadow-lg p-6">
                    <div class="basis-full mb-4">
                        <?php echo Bleet::toggle()
                            ->active($formModel, 'active')
                            ->primary()
                            ->render();
                        ?>
                    </div>

                    <div class="flex gap-4">
                        <div class="basis-4/6 flex gap-4">
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
                        <div class="basis-2/6 flex gap-4">
                            <div class="basis-1/2">
                                <?php echo Bleet::label()
                                        ->active($formModel, 'languageId')
                                        ->primary()
                                        ->render();
                                ?>
                                <div class="mt-2">
                                    <?php echo Bleet::select()
                                            ->active($formModel, 'languageId')
                                            ->options($languageOptions)
                                            ->placeholder($translator->translate('-- Select --', category: 'dboard-common'))
                                            ->primary()
                                            ->render();
                                    ?>
                                </div>
                            </div>
                            <div class="basis-1/2">
                                <?php echo Bleet::label()
                                    ->active($formModel, 'typeId')
                                    ->primary()
                                    ->render();
                                ?>
                                <div class="mt-2">
                                    <?php echo Bleet::select()
                                        ->active($formModel, 'typeId')
                                        ->options($typeOptions)
                                        ->placeholder($translator->translate('-- None --', category: 'dboard-common'))
                                        ->primary()
                                        ->render();
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('Properties', category: 'dboard-content'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-full">
                            <?php echo Bleet::label()
                                    ->active($formModel, 'elasticSchemaId')
                                    ->primary()
                                    ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::select()
                                        ->active($formModel, 'elasticSchemaId')
                                        ->options($elasticSchemaOptions)
                                        ->placeholder($translator->translate('-- None --', category: 'dboard-common'))
                                        ->primary()
                                        ->render();
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($targetOptions)): ?>
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
                                        ->options($targetOptions)
                                        ->placeholder($isEdit ? $translator->translate('-- Select --', category: 'dboard-common') : $translator->translate('-- Root --', category: 'dboard-common'))
                                        ->searchable()
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
