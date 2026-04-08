<?php

declare(strict_types=1);

/**
 * _translations-list-content.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Forms\TranslationForm;
use Blackcube\Bleet\Bleet;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var TranslatorInterface $translator
 * @var ActiveRecord $model
 * @var TranslationForm[] $translationForms
 * @var ActiveRecord[] $orphans
 * @var TranslationForm $linkFormModel
 * @var UrlGeneratorInterface $urlGenerator
 * @var string $formAction
 * @var string $translationsListId
 * @var string|null $csrf
 */

$orphanOptions = [];
foreach ($orphans as $orphan) {
    $orphanOptions[$orphan->getId()] = $orphan->getLanguageId() . ' — ' . $orphan->getName();
}
?>

<!-- Current translations -->
<?php if (!empty($translationForms)): ?>
    <div class="space-y-2 mb-6">
        <h4 class="text-sm font-medium text-secondary-700"><?php echo $translator->translate('Linked translations', category: 'dboard-common'); ?></h4>
        <?php foreach ($translationForms as $form): ?>
            <div class="flex items-center justify-between p-3 bg-secondary-50 rounded-lg">
                <div>
                    <span class="font-medium"><?php echo Html::encode($form->getTargetLanguageId()); ?></span>
                    <span class="text-secondary-600"> — </span>
                    <span class="text-sm text-secondary-700"><?php echo Html::encode($form->getTargetName()); ?></span>
                </div>
                <?php echo Html::form()
                    ->method('DELETE')
                    ->action($formAction)
                    ->csrf($csrf)
                    ->noValidate()
                    ->open(); ?>
                    <?php echo Bleet::input()
                        ->hidden()
                        ->active($form, 'targetId')
                        ->render(); ?>
                    <?php echo Bleet::button()
                        ->icon('x-mark')
                        ->submit()
                        ->outline()
                        ->xs()
                        ->danger()
                        ->render(); ?>
                <?php echo Html::closeTag('form'); ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="text-sm text-secondary-500 italic mb-6"><?php echo $translator->translate('No linked translation.', category: 'dboard-common'); ?></p>
<?php endif; ?>

<!-- Link new translation -->
<?php if (!empty($orphanOptions)): ?>
    <div>
        <h4 class="text-sm font-medium text-secondary-700 mb-2"><?php echo $translator->translate('Link a translation', category: 'dboard-common'); ?></h4>
        <?php echo Html::form()
            ->method('POST')
            ->action($formAction)
            ->csrf($csrf)
            ->noValidate()
            ->open(); ?>
            <div class="flex gap-2">
                <?php echo Bleet::select()
                    ->active($linkFormModel, 'targetId')
                    ->options($orphanOptions)
                    ->placeholder($translator->translate('-- Select --', category: 'dboard-common'))
                    ->searchable()
                    ->secondary()
                    ->wrapperAddClass('flex-1')
                    ->render(); ?>
                <?php echo Bleet::button($translator->translate('Link', category: 'dboard-common'))
                    ->submit()
                    ->primary()
                    ->xs()
                    ->render(); ?>
            </div>
        <?php echo Html::closeTag('form'); ?>
    </div>
<?php else: ?>
    <p class="text-sm text-secondary-500 italic"><?php echo $translator->translate('No content available for linking.', category: 'dboard-common'); ?></p>
<?php endif; ?>
