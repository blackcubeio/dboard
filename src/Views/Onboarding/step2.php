<?php

declare(strict_types=1);

/**
 * step2.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var array $data
 */

?>
<div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl p-8">
    <div class="mb-8">
        <?php echo Bleet::step()
            ->current(1)
            ->addStep($translator->translate('Information', category: 'dboard-onboarding'), $urlGenerator->generate('dboard.onboarding.step1'))
            ->addStep($translator->translate('Verification', category: 'dboard-onboarding'), $urlGenerator->generate('dboard.onboarding.step2'))
            ->addStep($translator->translate('Confirmation', category: 'dboard-onboarding'), $urlGenerator->generate('dboard.onboarding.step3'))
            ->addStep($translator->translate('Finished', category: 'dboard-onboarding'), $urlGenerator->generate('dboard.onboarding.finish'))
            ->render();
        ?>
    </div>

    <div class="text-center mb-8">
        <div class="flex justify-center mb-4">
            <div class="bg-black rounded-xl p-2 text-white">
                <?php echo Bleet::svg()
                    ->logo('blackcube')
                    ->addClass('size-16');
                ?>
            </div>
        </div>
        <h2 class="text-2xl font-bold text-gray-900"><?php echo $translator->translate('Verification', category: 'dboard-onboarding'); ?></h2>
        <p class="text-gray-600 mt-2"><?php echo $translator->translate('Verify the information entered', category: 'dboard-onboarding'); ?></p>
    </div>

    <div class="space-y-4 mb-8">
        <div class="border border-gray-200 rounded-lg p-4">
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500"><?php echo $translator->translate('First name', category: 'dboard-common'); ?></dt>
                    <dd class="text-sm text-gray-900"><?php echo Html::encode($data['firstname'] ?? '') ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500"><?php echo $translator->translate('Last name', category: 'dboard-common'); ?></dt>
                    <dd class="text-sm text-gray-900"><?php echo Html::encode($data['lastname'] ?? '') ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">E-mail</dt>
                    <dd class="text-sm text-gray-900"><?php echo Html::encode($data['email'] ?? '') ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500"><?php echo $translator->translate('Password', category: 'dboard-common'); ?></dt>
                    <dd class="text-sm text-gray-900">••••••••</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="flex gap-4">
        <?php echo Bleet::a($translator->translate('Previous', category: 'dboard-common'), $urlGenerator->generate('dboard.onboarding.step1'))
            ->button()
            ->secondary()
            ->addClass('flex-1')
            ->render();
        ?>
        <?php echo Bleet::a($translator->translate('Confirm', category: 'dboard-common'), $urlGenerator->generate('dboard.onboarding.step3'))
            ->button()
            ->primary()
            ->addClass('flex-1')
            ->render();
        ?>
    </div>
</div>
