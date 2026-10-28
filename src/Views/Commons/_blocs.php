<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dboard\Enums\ReorderMode;
use Blackcube\Dboard\Helpers\ElasticFieldRenderer;
use Blackcube\Dboard\Models\Forms\BlocForm;
use Blackcube\Html\Aurelia;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\AjaxifyTriggerMode;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var ActiveRecord $model The parent entity (Content, Tag, etc.)
 * @var BlocForm[] $blocForms Form models keyed by bloc ID
 * @var ElasticSchema[] $allowedElasticSchemas
 * @var string|null $csrf
 * @var bool $dndMode Whether drag-and-drop mode is active
 * @var string $reorderRoute Route name for reorder action
 * @var string $addRoute Route name for add bloc action
 * @var string $deleteRoute Route name for delete bloc action
 * @var array $fileEndpoints File upload/preview/delete endpoints
 * @var string $blocsListId DOM element ID for the blocs list container
 * @var string $routeIdParam Route parameter name for entity ID (default: 'id')
 */

$dndMode = $dndMode ?? false;
$routeIdParam = $routeIdParam ?? 'id';
$blocsCount = count($blocForms);
$modelId = $model->getId();
$blocFormHelper = new BlocForm();
$reorderUrl = $urlGenerator->generate($reorderRoute, [
    $routeIdParam => $modelId,
]);
$addUrl = $urlGenerator->generate($addRoute, [
    $routeIdParam => $modelId,
]);
$schemaOptions = [];
foreach ($allowedElasticSchemas as $schema):
    $schemaOptions[$schema->getId()] = $schema->getName();
endforeach;
?>

<?php if ($blocsCount > 0): ?>
    <?php echo Html::openTag('div', [
        'class' => 'space-y-4',
        'dboard-drag-drop' => Aurelia::attributesCustomAttribute([
            'url' => $reorderUrl,
            'id' => $blocsListId,
            'dndMode' => $dndMode === true ? true : null,
            'csrf' => $csrf,
            'errorTitle' => $translator->translate('Error', category: 'dboard-common'),
            'errorContent' => $translator->translate('Reordering failed.', category: 'dboard-common'),
        ]),
    ]); ?>
        <?php $i = 0; foreach ($blocForms as $blocId => $blocForm): ?>
            <?php
            $blocContainerClasses = [
                'border',
                'border-secondary-200',
                'rounded-lg',
            ];
            if ($blocForm->isActive() === false):
                $blocContainerClasses[] = 'inactive';
            endif;
            if ($dndMode === true):
                $blocContainerClasses[] = 'relative';
            endif;
            ?>
            <?php echo Html::openTag('div', [
                'class' => implode(' ', $blocContainerClasses),
                'data-drag-drop' => 'item-' . $blocId,
            ]); ?>
                <div class="dark dboard-band flex items-center justify-between rounded-t-lg px-4 py-2">
                    <div class="flex items-center gap-2">
                        <?php
                        $handleClasses = [
                            'flex-shrink-0',
                        ];
                        if ($dndMode === true):
                            $handleClasses[] = 'text-primary-600';
                        else:
                            $handleClasses[] = 'invisible';
                        endif;
                        echo Html::tag('span', Bleet::svg()->outline('arrows-up-down')->addClass('size-5', $dndMode === true ? 'text-primary-600' : 'text-secondary-400'))
                            ->class(...$handleClasses)
                            ->attribute('data-drag-handle', true)
                            ->encode(false);
                        ?>
                        <h4 class="text-base font-semibold text-white">
                            <?php echo Html::encode(ElasticFieldRenderer::getSchemaName($blocForm->getElasticSchemaId()) ?? $translator->translate('Block', category: 'dboard-common')); ?>
                        </h4>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if ($i > 0): ?>
                            <?php echo Html::openTag('span', Bleet::ajaxify()->mode(AjaxifyTriggerMode::PublishOnly)->target($blocsListId)->event('click')->trigger()); ?>
                                <?php echo Html::input('hidden', 'BlocForm[blocId]', (string) $blocId, [
                                    'disabled' => true,
                                ]); ?>
                                <?php echo Html::input('hidden', 'BlocForm[mode]', ReorderMode::MoveUp->value, [
                                    'disabled' => true,
                                ]); ?>
                                <?php echo Bleet::button()
                                    ->icon('chevron-up')
                                    ->outline()
                                    ->xs()
                                    ->secondary()
                                    ->render();
                                ?>
                            <?php echo Html::closeTag('span'); ?>
                        <?php else: ?>
                            <?php echo Bleet::button()
                                ->icon('chevron-up')
                                ->disabled()
                                ->outline()
                                ->xs()
                                ->secondary()
                                ->render();
                            ?>
                        <?php endif; ?>

                        <?php if ($i < $blocsCount - 1): ?>
                            <?php echo Html::openTag('span', Bleet::ajaxify()->mode(AjaxifyTriggerMode::PublishOnly)->target($blocsListId)->event('click')->trigger()); ?>
                                <?php echo Html::input('hidden', 'BlocForm[blocId]', (string) $blocId, [
                                    'disabled' => true,
                                ]); ?>
                                <?php echo Html::input('hidden', 'BlocForm[mode]', ReorderMode::MoveDown->value, [
                                    'disabled' => true,
                                ]); ?>
                                <?php echo Bleet::button()
                                    ->icon('chevron-down')
                                    ->outline()
                                    ->xs()
                                    ->secondary()
                                    ->render();
                                ?>
                            <?php echo Html::closeTag('span'); ?>
                        <?php else: ?>
                            <?php echo Bleet::button()
                                ->icon('chevron-down')
                                ->disabled()
                                ->outline()
                                ->xs()
                                ->secondary()
                                ->render();
                            ?>
                        <?php endif; ?>

                        <?php
                        $deleteUrl = $urlGenerator->generate($deleteRoute, [
                            $routeIdParam => $modelId,
                            'blocId' => $blocId,
                        ]);
                        echo Bleet::button()
                            ->icon('trash')
                            ->addAttributes(Bleet::modal()->trigger($deleteUrl))
                            ->outline()
                            ->xs()
                            ->danger()
                            ->render();
                        ?>

                        <?php if (empty($schemaOptions) === false): ?>
                            <span class="ml-4 inline-flex items-center gap-1">
                                <?php echo Bleet::select()
                                    ->optionsData($schemaOptions)
                                    ->prompt('--')
                                    ->fieldData([
                                        'ajaxify' => 'addBloc-' . $blocId,
                                        'ajaxify-target' => 'BlocForm[elasticSchemaId]',
                                    ])
                                    ->secondary()
                                    ->hideLabel()
                                    ->addContainerClass('w-56')
                                    ->render();
                                ?>
                                <?php echo Html::openTag('span', Bleet::ajaxify()->mode(AjaxifyTriggerMode::PublishOnly)->target($blocsListId)->id('addBloc-' . $blocId)->event('click')->trigger()); ?>
                                    <?php echo Html::input('hidden', 'BlocForm[elasticSchemaId]', '', [
                                        'disabled' => true,
                                    ]); ?>
                                    <?php echo Html::input('hidden', 'BlocForm[blocAdd]', (string) $blocId, [
                                        'disabled' => true,
                                    ]); ?>
                                    <?php echo Bleet::button()
                                        ->icon('plus')
                                        ->outline()
                                        ->xs()
                                        ->primary()
                                        ->render();
                                    ?>
                                <?php echo Html::closeTag('span'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
                $blocContentClasses = [
                    'p-4',
                    'rounded-b-lg',
                ];
                $blocContentInlineStyle = '';
                if ($blocForm->isActive() === true):
                    $blocContentClasses[] = 'bg-white';
                else:
                    $blocContentInlineStyle = 'background: repeating-linear-gradient(-45deg, transparent, transparent 8px, var(--color-warning-100) 8px, var(--color-warning-100) 9px);';
                endif;
                if ($dndMode === true):
                    $blocContentClasses[] = 'overflow-hidden';
                endif;
                if ($dndMode === true):
                    $blocContentInlineStyle .= ' height: 70px;';
                endif;
                ?>
                <?php echo Html::openTag('div', [
                    'class' => implode(' ', $blocContentClasses),
                    'style' => trim($blocContentInlineStyle),
                ]); ?>
                    <?php
                    $adminTemplate = ElasticFieldRenderer::getAdminView($blocForm->getElasticSchemaId());
                    if ($adminTemplate !== false):
                        $formClass = get_class($blocForm);
                        $elasticAttributes = [];
                        foreach ($blocForm->getProperties() as $propertyName => $property):
                            if ($property->isElastic($formClass) === true):
                                $elasticAttributes[] = $propertyName;
                            endif;
                        endforeach;
                        echo $this->render($adminTemplate, [
                            'blocId' => $blocId,
                            'blocForm' => $blocForm,
                            'attributes' => $elasticAttributes,
                            'fileEndpoints' => $fileEndpoints,
                        ]);
                    else:
                        echo ElasticFieldRenderer::renderAll($blocForm, '[' . $blocId . ']', $fileEndpoints);
                    endif;
                    ?>
                <?php echo Html::closeTag('div'); ?>
                <?php if ($dndMode === true): ?>
                    <div class="dnd-handle-layer absolute inset-0 bg-primary-500/10 z-20 cursor-grab"></div>
                <?php endif; ?>
            <?php echo Html::closeTag('div'); ?>
        <?php $i++; endforeach; ?>
    <?php echo Html::closeTag('div'); ?>
<?php endif; ?>

<?php if ($blocsCount === 0 && empty($schemaOptions) === false): ?>
    <div class="flex justify-end">
        <span class="inline-flex items-center gap-1">
            <?php echo Bleet::select()
                ->optionsData($schemaOptions)
                ->prompt($translator->translate('-- Block type --', category: 'dboard-common'))
                ->fieldData([
                    'ajaxify' => 'addBloc-first',
                    'ajaxify-target' => 'BlocForm[elasticSchemaId]',
                ])
                ->secondary()
                ->hideLabel()
                ->addContainerClass('w-56')
                ->render();
            ?>
            <?php echo Html::openTag('span', Bleet::ajaxify()->mode(AjaxifyTriggerMode::PublishOnly)->target($blocsListId)->id('addBloc-first')->event('click')->trigger()); ?>
                <?php echo Html::input('hidden', 'BlocForm[elasticSchemaId]', '', [
                    'disabled' => true,
                ]); ?>
                <?php echo Bleet::button($translator->translate('Add', category: 'dboard-common'))
                    ->icon('plus')
                    ->outline()
                    ->xs()
                    ->primary()
                    ->render();
                ?>
            <?php echo Html::closeTag('span'); ?>
        </span>
    </div>
<?php elseif ($blocsCount === 0 && empty($allowedElasticSchemas) === true): ?>
    <p class="text-sm text-secondary-500 italic"><?php echo $translator->translate('No block type available for this type.', category: 'dboard-common'); ?></p>
<?php endif; ?>
