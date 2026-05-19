<?php

declare(strict_types=1);

/**
 * _refresh-content.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var TranslatorInterface $translator
 * @var string $formAction
 * @var string|null $csrf
 */
?>
<div class="p-6 space-y-4">

    <?php echo Bleet::p($translator->translate('Roles and permissions will be synchronized with the source code. Missing items will be added and obsolete items will be removed. Existing assignments will not be modified.', category: 'dboard-modules')) ?>

    <div class="flex gap-4 pt-4 border-t border-gray-200">
        <?php echo Html::form()
                ->post($formAction)
                ->noValidate()
                ->open(); ?>
        <?php echo Bleet::button($translator->translate('Close', category: 'dboard-common'))
                ->secondary()
                ->fieldData(['modal' => 'close']) ?>

        <?php echo Bleet::button($translator->translate('Refresh', category: 'dboard-common'))
                ->submit()
                ->warning(); ?>
        <?php echo Html::closeTag('form'); ?>
    </div>
</div>
