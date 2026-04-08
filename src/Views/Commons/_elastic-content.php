<?php

declare(strict_types=1);

/**
 * _elastic-content.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dboard\Helpers\ElasticFieldRenderer;
use Blackcube\Bleet\Bleet;
use Blackcube\BridgeModel\BridgeFormModel;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var TranslatorInterface $translator
 * @var ActiveRecord $model
 * @var BridgeFormModel $formModel
 * @var UrlGeneratorInterface $urlGenerator
 * @var array $fileEndpoints
 * @var string $elasticSchemaName
 * @var ElasticSchema|null $elasticSchema
 * @var string $formAction
 * @var string|null $csrf
 */
?>
<div class="p-6">
    <?php echo Html::form()
        ->method('POST')
        ->action($formAction)
        ->csrf($csrf)
        ->noValidate()
        ->open(); ?>

    <div class="mb-4">
        <p class="text-sm text-secondary-600">
            <?php echo $translator->translate('Schema:', category: 'dboard-common'); ?> <strong><?php echo Html::encode($elasticSchemaName); ?></strong>
        </p>
    </div>

    <div class="space-y-4">
        <?php
        $elasticSchema = $elasticSchema ?? null;
        $adminTemplate = ElasticFieldRenderer::getAdminView($elasticSchema);
        if ($adminTemplate !== false) {
            $formClass = get_class($formModel);
            $elasticProperties = array_filter($formModel->getProperties(), function ($property) use ($formClass) {
                return $property->isElastic($formClass);
            });
            $elasticAttributes = array_keys($elasticProperties);
            echo $this->render($adminTemplate, [
                'blocId' => null,
                'blocForm' => $formModel,
                'attributes' => $elasticAttributes,
                'fileEndpoints' => $fileEndpoints,
            ]);
        } else {
            echo ElasticFieldRenderer::renderAll($formModel, '', $fileEndpoints);
        }
        ?>
    </div>

    <div class="flex gap-4 pt-6 mt-6 border-t border-gray-200">
        <?php echo Bleet::button($translator->translate('Close', category: 'dboard-common'))
            ->secondary()
            ->attribute('data-drawer', 'close')
            ->render(); ?>

        <?php echo Bleet::button($translator->translate('Save', category: 'dboard-common'))
            ->submit()
            ->primary()
            ->render(); ?>
    </div>

    <?php echo Html::closeTag('form'); ?>
</div>
