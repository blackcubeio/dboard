<?php

declare(strict_types=1);

/**
 * _blocs.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dboard\Enums\ReorderMode;
use Blackcube\Dboard\Helpers\ElasticFieldRenderer;
use Blackcube\Dboard\Models\Forms\BlocForm;
use Blackcube\Bleet\Aurelia;
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
$reorderUrl = $urlGenerator->generate($reorderRoute, [$routeIdParam => $modelId]);
$addUrl = $urlGenerator->generate($addRoute, [$routeIdParam => $modelId]);
$schemaOptions = [];
foreach ($allowedElasticSchemas as $schema) {
    $schemaOptions[$schema->getId()] = $schema->getName();
}
?>

<?php if ($blocsCount > 0): ?>
    <?php echo Html::openTag('div', [
        'class' => 'space-y-4',
        'dboard-drag-drop' => Aurelia::attributesCustomAttribute([
            'url' => $reorderUrl,
            'id' => $blocsListId,
            'dndMode' => $dndMode ?: null,
            'csrf' => $csrf,
            'errorTitle' => $translator->translate('Error', category: 'dboard-common'),
            'errorContent' => $translator->translate('Reordering failed.', category: 'dboard-common'),
        ]),
    ]); ?>
        <?php $i = 0; foreach ($blocForms as $blocId => $blocForm): ?>
            <?php
            $blocContainerClasses = ['border', 'border-secondary-200', 'rounded-lg'];
            if (!$blocForm->isActive()) {
                $blocContainerClasses[] = 'inactive';
            }
            if ($dndMode) {
                $blocContainerClasses[] = 'relative';
            }
            ?>
            <?php echo Html::openTag('div', [
                'class' => implode(' ', $blocContainerClasses),
                'data-drag-drop' => 'item-' . $blocId,
            ]); ?>
                <div class="bg-secondary-100 px-4 py-2 flex items-center justify-between rounded-t-lg">
                    <div class="flex items-center gap-2">
                        <?php
                        $handleClasses = ['flex-shrink-0'];
                        if ($dndMode) {
                            $handleClasses[] = 'text-primary-600';
                        } else {
                            $handleClasses[] = 'invisible';
                        }
                        echo Html::tag('span', Bleet::svg()->outline('arrows-up-down')->addClass('size-5', $dndMode ? 'text-primary-600' : 'text-secondary-400'))
                            ->class(...$handleClasses)
                            ->attribute('data-drag-handle', true)
                            ->encode(false);
                        ?>
                        <h4 class="font-medium text-secondary-700">
                            <?php echo Html::encode(ElasticFieldRenderer::getSchemaName($blocForm->getElasticSchemaId()) ?? $translator->translate('Block', category: 'dboard-common')); ?>
                        </h4>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php // Up button - publishOnly ?>
                        <?php if ($i > 0): ?>
                            <?php echo Html::openTag('span', Bleet::ajaxify()->mode(AjaxifyTriggerMode::PublishOnly)->target($blocsListId)->event('click')->trigger()); ?>
                                <?php echo Html::input('hidden', 'BlocForm[blocId]', (string) $blocId, ['disabled' => true]); ?>
                                <?php echo Html::input('hidden', 'BlocForm[mode]', ReorderMode::MoveUp->value, ['disabled' => true]); ?>
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

                        <?php // Down button - publishOnly ?>
                        <?php if ($i < $blocsCount - 1): ?>
                            <?php echo Html::openTag('span', Bleet::ajaxify()->mode(AjaxifyTriggerMode::PublishOnly)->target($blocsListId)->event('click')->trigger()); ?>
                                <?php echo Html::input('hidden', 'BlocForm[blocId]', (string) $blocId, ['disabled' => true]); ?>
                                <?php echo Html::input('hidden', 'BlocForm[mode]', ReorderMode::MoveDown->value, ['disabled' => true]); ?>
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

                        <?php // Delete button ?>
                        <?php
                        $deleteUrl = $urlGenerator->generate($deleteRoute, [$routeIdParam => $modelId, 'blocId' => $blocId]);
                        echo Bleet::button()
                            ->icon('trash')
                            ->addAttributes(Bleet::modal()->trigger($deleteUrl))
                            ->outline()
                            ->xs()
                            ->danger()
                            ->render();
                        ?>

                        <?php // Add bloc button - publishOnly ?>
                        <?php if (!empty($schemaOptions)): ?>
                            <span class="ml-4 inline-flex items-center gap-1">
                                <?php echo Bleet::select()
                                    ->options($schemaOptions)
                                    ->placeholder('--')
                                    ->fieldData(['ajaxify' => 'addBloc-' . $blocId, 'ajaxify-target' => 'BlocForm[elasticSchemaId]'])
                                    ->secondary()
                                    ->wrapperAddClass('w-32')
                                    ->render();
                                ?>
                                <?php echo Html::openTag('span', Bleet::ajaxify()->mode(AjaxifyTriggerMode::PublishOnly)->target($blocsListId)->id('addBloc-' . $blocId)->event('click')->trigger()); ?>
                                    <?php echo Html::input('hidden', 'BlocForm[elasticSchemaId]', '', ['disabled' => true]); ?>
                                    <?php echo Html::input('hidden', 'BlocForm[blocAdd]', (string) $blocId, ['disabled' => true]); ?>
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
                $blocContentClasses = ['p-4', 'rounded-b-lg'];
                $blocContentInlineStyle = '';
                if ($blocForm->isActive()) {
                    $blocContentClasses[] = 'bg-white';
                } else {
                    $blocContentInlineStyle = 'background: repeating-linear-gradient(-45deg, transparent, transparent 8px, var(--color-warning-100) 8px, var(--color-warning-100) 9px);';
                }
                if ($dndMode) {
                    $blocContentClasses[] = 'overflow-hidden';
                }
                if ($dndMode) {
                    $blocContentInlineStyle .= ' height: 70px;';
                }
                ?>
                <?php echo Html::openTag('div', ['class' => implode(' ', $blocContentClasses), 'style' => trim($blocContentInlineStyle)]); ?>
                    <?php
                    $adminTemplate = ElasticFieldRenderer::getAdminView($blocForm->getElasticSchemaId());
                    if ($adminTemplate !== false) {
                        $formClass = get_class($blocForm);
                        $elasticProperties = array_filter($blocForm->getProperties(), function ($property) use ($formClass) {
                            return $property->isElastic($formClass);
                        });
                        $elasticAttributes = array_keys($elasticProperties);
                        echo $this->render($adminTemplate, [
                            'blocId' => $blocId,
                            'blocForm' => $blocForm,
                            'attributes' => $elasticAttributes,
                            'fileEndpoints' => $fileEndpoints,
                        ]);
                    } else {
                        echo ElasticFieldRenderer::renderAll($blocForm, '[' . $blocId . ']', $fileEndpoints);
                    }
                    ?>
                <?php echo Html::closeTag('div'); ?>
                <?php if ($dndMode): ?>
                    <div class="dnd-handle-layer absolute inset-0 bg-primary-500/10 z-20 cursor-grab"></div>
                <?php endif; ?>
            <?php echo Html::closeTag('div'); ?>
        <?php $i++; endforeach; ?>
    <?php echo Html::closeTag('div'); ?>
<?php endif; ?>

<?php if ($blocsCount === 0 && !empty($schemaOptions)): ?>
    <div class="flex justify-end">
        <span class="inline-flex items-center gap-1">
            <?php echo Bleet::select()
                ->options($schemaOptions)
                ->placeholder($translator->translate('-- Block type --', category: 'dboard-common'))
                ->fieldData(['ajaxify' => 'addBloc-first', 'ajaxify-target' => 'BlocForm[elasticSchemaId]'])
                ->secondary()
                ->wrapperAddClass('w-48')
                ->render();
            ?>
            <?php echo Html::openTag('span', Bleet::ajaxify()->mode(AjaxifyTriggerMode::PublishOnly)->target($blocsListId)->id('addBloc-first')->event('click')->trigger()); ?>
                <?php echo Html::input('hidden', 'BlocForm[elasticSchemaId]', '', ['disabled' => true]); ?>
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
<?php elseif ($blocsCount === 0 && empty($allowedElasticSchemas)): ?>
    <p class="text-sm text-secondary-500 italic"><?php echo $translator->translate('No block type available for this type.', category: 'dboard-common'); ?></p>
<?php endif; ?>
