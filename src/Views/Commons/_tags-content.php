<?php

declare(strict_types=1);

/**
 * _tags-content.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var TranslatorInterface $translator
 * @var ActiveRecord $model
 * @var array $pivotForms
 * @var string $pivotFkColumn
 * @var UrlGeneratorInterface $urlGenerator
 * @var string $formAction
 * @var string|null $csrf
 */
?>
<div class="p-6">
    <?php echo Html::form()->post($formAction)->csrf($csrf)->noValidate()->open(); ?>
    <div class="space-y-4">
        <?php foreach ($pivotForms as $index => $form): ?>
            <?php if (((int)$form->getTagLevel()) === 1): ?>
                <?php echo Bleet::h5($form->getTagName())->addClass('text-secondary-700', 'border-b', 'border-secondary-200', 'pb-2', 'mt-4'); ?>
            <?php else: ?>
                <div class="ml-4">
                    <?php echo Bleet::input()->hidden()->active($form, '['.$index.']tagId')->render(); ?>
                    <?php echo Bleet::input()->hidden()->active($form, '['.$index.']'.$pivotFkColumn)->render(); ?>
                    <?php echo Bleet::input()->hidden()->active($form, '['.$index.']tagLevel')->render(); ?>
                    <?php echo Bleet::toggle()
                            ->active($form, '['.$index.']selected')
                            ->value('1')
                            ->secondary()
                            ->render();
                    ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <div class="flex gap-4 pt-6 mt-6 border-t border-gray-200">
        <?php echo Bleet::button($translator->translate('Close', category: 'dboard-common'))->secondary()->attribute('data-drawer', 'close')->render(); ?>
        <?php echo Bleet::button($translator->translate('Save', category: 'dboard-common'))->submit()->primary()->render(); ?>
    </div>
    <?php echo Html::closeTag('form'); ?>
</div>
