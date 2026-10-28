<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Forms\OnboardingForm;
use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var OnboardingForm $model
 * @var string|null $csrf
 */

?>
<div class="dboard-surface p-8">
    <div class="mb-8">
        <?php echo Bleet::step()
            ->current(0)
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
        <h2 class="text-2xl font-semibold tracking-tight text-secondary-950 dark:text-white"><?php echo $translator->translate('Setup', category: 'dboard-onboarding'); ?></h2>
        <p class="mt-2 text-secondary-500 dark:text-secondary-400"><?php echo $translator->translate('Create your administrator account', category: 'dboard-onboarding'); ?></p>
    </div>

    <?php echo Html::form()
        ->post($urlGenerator->generate('dboard.onboarding.step1'))
        ->csrf($csrf)
        ->noValidate()
        ->addClass('space-y-6', 'max-w-xl', 'mx-auto', 'my-8')
        ->open();
    ?>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <?php echo Bleet::input($model, 'firstname')
                    ->text()
                    ->render();
                ?>
            </div>
            <div>
                <?php echo Bleet::input($model, 'lastname')
                    ->text()
                    ->render();
                ?>
            </div>
        </div>

        <div>
            <?php echo Bleet::input($model, 'email')
                ->email()
                ->render();
            ?>
        </div>

        <div>
            <?php echo Bleet::input($model, 'password')
                ->password()
                ->showable()
                ->render();
            ?>
        </div>

        <div>
            <?php echo Bleet::input($model, 'passwordConfirm')
                ->password()
                ->showable()
                ->render();
            ?>
        </div>

        <div class="pt-4">
            <?php echo Bleet::submitButton($translator->translate('Next', category: 'dboard-common'))
                ->accent()
                ->addClass('w-full')
                ->render();
            ?>
        </div>
    <?php echo Html::form()->close(); ?>
</div>
