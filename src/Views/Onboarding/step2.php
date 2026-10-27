<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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
 * @var array $administratorValues
 */

?>
<div class="dboard-surface p-8">
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
            <div class="rounded-xl bg-primary-950 p-2 text-white dark:bg-white/10">
                <?php echo Bleet::svg()
                    ->logo('blackcube')
                    ->addClass('size-16');
                ?>
            </div>
        </div>
        <h2 class="text-2xl font-semibold tracking-tight text-secondary-950 dark:text-white"><?php echo $translator->translate('Verification', category: 'dboard-onboarding'); ?></h2>
        <p class="mt-2 text-secondary-500 dark:text-secondary-400"><?php echo $translator->translate('Verify the information entered', category: 'dboard-onboarding'); ?></p>
    </div>

    <div class="space-y-4 mb-8">
        <div class="rounded-lg border border-secondary-200 p-4 dark:border-white/10">
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400"><?php echo $translator->translate('First name', category: 'dboard-common'); ?></dt>
                    <dd class="text-sm text-secondary-900 dark:text-white"><?php echo Html::encode($administratorValues['firstname'] ?? ''); ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400"><?php echo $translator->translate('Last name', category: 'dboard-common'); ?></dt>
                    <dd class="text-sm text-secondary-900 dark:text-white"><?php echo Html::encode($administratorValues['lastname'] ?? ''); ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400">E-mail</dt>
                    <dd class="text-sm text-secondary-900 dark:text-white"><?php echo Html::encode($administratorValues['email'] ?? ''); ?></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-secondary-500 dark:text-secondary-400"><?php echo $translator->translate('Password', category: 'dboard-common'); ?></dt>
                    <dd class="text-sm text-secondary-900 dark:text-white">••••••••</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="flex gap-4">
        <?php echo Bleet::a($translator->translate('Previous', category: 'dboard-common'), $urlGenerator->generate('dboard.onboarding.step1'))
            ->button()
            ->ghost()
            ->secondary()
            ->addClass('flex-1', 'text-center')
            ->render();
        ?>
        <?php echo Bleet::a($translator->translate('Confirm', category: 'dboard-common'), $urlGenerator->generate('dboard.onboarding.step3'))
            ->button()
            ->accent()
            ->addClass('flex-1', 'text-center')
            ->render();
        ?>
    </div>
</div>
