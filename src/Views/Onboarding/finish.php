<?php

declare(strict_types=1);

/**
 * finish.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var string|null $csrf
 */

?>
<div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl p-8">
    <div class="mb-8">
        <?php echo Bleet::step()
            ->current(3)
            ->addStep($translator->translate('Information', category: 'dboard-onboarding'), $urlGenerator->generate('dboard.onboarding.step1'))
            ->addStep($translator->translate('Verification', category: 'dboard-onboarding'), $urlGenerator->generate('dboard.onboarding.step2'))
            ->addStep($translator->translate('Confirmation', category: 'dboard-onboarding'), $urlGenerator->generate('dboard.onboarding.step3'))
            ->addStep($translator->translate('Finished', category: 'dboard-onboarding'), $urlGenerator->generate('dboard.onboarding.finish'))
            ->render();
        ?>
    </div>

    <div class="text-center">
        <div class="flex justify-center mb-4">
            <div class="bg-black rounded-xl p-2 text-white">
                <?php echo Bleet::svg()
                    ->logo('blackcube')
                    ->addClass('size-16');
                ?>
            </div>
        </div>
        <div class="flex justify-center mb-4">
            <div class="bg-success-100 rounded-full p-3">
                <?php echo Bleet::svg()
                    ->solid('check-circle')
                    ->addClass('size-16', 'text-success-600');
                ?>
            </div>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2"><?php echo $translator->translate('Setup complete!', category: 'dboard-onboarding'); ?></h2>
        <p class="text-gray-600 mb-8"><?php echo $translator->translate('Your administrator account has been created successfully.', category: 'dboard-onboarding'); ?></p>

        <?php echo Bleet::a($translator->translate('Sign in', category: 'dboard-modules'), $urlGenerator->generate('dboard.login'))
            ->primary()
            ->addClass('inline-flex')
            ->render();
        ?>
    </div>
</div>
