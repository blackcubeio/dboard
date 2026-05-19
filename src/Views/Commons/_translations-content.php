<?php

declare(strict_types=1);

/**
 * _translations-content.php
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
?>
<div class="p-6">
    <?php echo Html::openTag('bleet-ajaxify', ['id' => $translationsListId]); ?>
        <?php echo $this->render('Commons/_translations-list-content', [
            'model' => $model,
            'translationForms' => $translationForms,
            'orphans' => $orphans,
            'linkFormModel' => $linkFormModel,
            'urlGenerator' => $urlGenerator,
            'formAction' => $formAction,
            'translationsListId' => $translationsListId,
            'csrf' => $csrf,
        ]); ?>
    <?php echo Html::closeTag('bleet-ajaxify'); ?>

    <div class="flex gap-4 pt-6 mt-6 border-t border-gray-200">
        <?php echo Bleet::button($translator->translate('Close', category: 'dboard-common'))
            ->secondary()
            ->attribute('data-drawer', 'close')
            ->render(); ?>
    </div>
</div>
