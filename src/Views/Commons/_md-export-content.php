<?php

declare(strict_types=1);

/**
 * _md-export-content.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Components\Rbac;
use Blackcube\Dboard\Models\Forms\MdExportForm;
use Blackcube\Bleet\Aurelia;
use Blackcube\Bleet\Bleet;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Html\Html;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var TranslatorInterface $translator
 * @var ActiveRecord $model
 * @var MdExportForm $formModel
 * @var string $entityLabel
 * @var string $formAction
 * @var string $importFormAction
 * @var array $fileEndpoints
 * @var string|null $csrf
 */

?>
<div class="p-6">
    <div class="space-y-6">

        <?php if ($userCan(Rbac::PERMISSION_IA_EXPORT)): ?>
        <!-- Download Section -->
        <div>
            <h4 class="text-sm font-semibold text-secondary-900 mb-4"><?php echo $translator->translate('Generate markdown', category: 'dboard-common'); ?></h4>
            <?php echo Html::form()
                ->method('POST')
                ->action($formAction)
                ->csrf($csrf)
                ->noValidate()
                ->attribute('dboard-md-download', Aurelia::attributesCustomAttribute([
                    'errorTitle' => $translator->translate('Error', category: 'dboard-common'),
                    'errorContent' => $translator->translate('Failed to download markdown.', category: 'dboard-common'),
                ]))
                ->open(); ?>
            <div class="space-y-4">
                <div>
                    <?php echo Bleet::label()->active($formModel, 'prompt')->secondary()->render(); ?>
                    <div class="mt-2">
                        <?php echo Bleet::textarea()
                            ->active($formModel, 'prompt')
                            ->rows(6)
                            ->secondary()
                            ->render(); ?>
                    </div>
                </div>
                <div>
                    <?php echo Bleet::button($translator->translate('Download markdown', category: 'dboard-common'))
                        ->submit()
                        ->icon('arrow-down-tray')
                        ->primary()
                        ->render();
                    ?>
                </div>
            </div>
            <?php echo Html::closeTag('form'); ?>
        </div>
        <?php endif; ?>

        <?php if ($userCan(Rbac::PERMISSION_IA_IMPORT)): ?>
        <div class="border-t border-gray-200"></div>

        <!-- Upload Section -->
        <div>
            <h4 class="text-sm font-semibold text-secondary-900 mb-4"><?php echo $translator->translate('Import a markdown', category: 'dboard-common'); ?></h4>
            <dboard-md-upload>
                <?php echo $this->render('Commons/_md-import-form-content', [
                    'importResult' => null,
                    'fileEndpoints' => $fileEndpoints,
                    'importFormAction' => $importFormAction,
                ]); ?>
            </dboard-md-upload>
        </div>

        <!-- Warning -->
        <div class="border-t border-gray-200 pt-4">
            <div class="rounded-md bg-warning-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <?php echo Bleet::svg()->outline('exclamation-triangle')->addClass('size-5', 'text-warning-400'); ?>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-warning-800">
                            <?php echo $translator->translate('Warning: the import will replace all existing blocks of this {entity}. If you do not want to put this {entity} at risk, please re-import the markdown into a new {entity} created with the same type and properties.', ['entity' => Html::encode($entityLabel)], 'dboard-common'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Close button -->
    <div class="flex gap-4 pt-6 mt-6 border-t border-gray-200">
        <?php echo Bleet::button($translator->translate('Close', category: 'dboard-common'))
            ->secondary()
            ->attribute('data-drawer', 'close')
            ->render();
        ?>
    </div>
</div>
