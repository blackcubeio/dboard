<?php

declare(strict_types=1);

/**
 * step1.php
 *
 * @copyright 2010-2026 Blackcube
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
<div class="bg-white/75 backdrop-blur-sm rounded-2xl shadow-2xl p-8">
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
            <div class="bg-black rounded-xl p-2 text-white">
                <?php echo Bleet::svg()
                    ->logo('blackcube')
                    ->addClass('size-16');
                ?>
            </div>
        </div>
        <h2 class="text-2xl font-bold text-gray-900"><?php echo $translator->translate('Setup', category: 'dboard-onboarding'); ?></h2>
        <p class="text-gray-600 mt-2"><?php echo $translator->translate('Create your administrator account', category: 'dboard-onboarding'); ?></p>
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
                <?php echo Bleet::label()
                    ->active($model, 'firstname')
                    ->render();
                ?>
                <div class="mt-2">
                    <?php echo Bleet::input()
                        ->active($model, 'firstname')
                        ->text()
                        ->render();
                    ?>
                </div>
            </div>
            <div>
                <?php echo Bleet::label()
                    ->active($model, 'lastname')
                    ->render();
                ?>
                <div class="mt-2">
                    <?php echo Bleet::input()
                        ->active($model, 'lastname')
                        ->text()
                        ->render();
                    ?>
                </div>
            </div>
        </div>

        <div>
            <?php echo Bleet::label()
                ->active($model, 'email')
                ->render();
            ?>
            <div class="mt-2">
                <?php echo Bleet::input()
                    ->active($model, 'email')
                    ->email()
                    ->render();
                ?>
            </div>
        </div>

        <div>
            <?php echo Bleet::label()
                ->active($model, 'password')
                ->render();
            ?>
            <div class="mt-2">
                <?php echo Bleet::input()
                    ->active($model, 'password')
                    ->password()
                    ->showable()
                    ->render();
                ?>
            </div>
        </div>

        <div>
            <?php echo Bleet::label()
                ->active($model, 'passwordConfirm')
                ->render();
            ?>
            <div class="mt-2">
                <?php echo Bleet::input()
                    ->active($model, 'passwordConfirm')
                    ->password()
                    ->showable()
                    ->render();
                ?>
            </div>
        </div>

        <div class="pt-4">
            <?php echo Bleet::button($translator->translate('Next', category: 'dboard-common'))
                ->submit()
                ->primary()
                ->addClass('w-full')
                ->render();
            ?>
        </div>
    <?php echo Html::form()->close(); ?>
</div>
