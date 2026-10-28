<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Forms\LoginForm;
use Blackcube\Html\Aurelia;
use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Translator\TranslatorInterface;

/**
 * The sign-in screen, split in two. On a large screen the left panel is the
 * ink of the sidebar: the brand, what the panel is for, a grid that fades and
 * an aurora of iris (.bg-grid and .bg-aurora in main.css); the form stands
 * alone on the workbench, to the right. On a phone the panel is gone and the
 * brand sits above the form.
 *
 * The passkey comes before the password. The dboard-login-device attribute
 * removes its whole block, separator included, when the browser cannot do
 * WebAuthn: the password form is always there.
 *
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var LoginForm $model
 * @var string|null $returnUrl
 * @var string|null $csrf
 */

$loginQuery = [];
if ($returnUrl !== null):
    $loginQuery['redirect'] = $returnUrl;
endif;
$loginDeviceAttributes = Aurelia::attributesCustomAttribute([
    'challengeUrl' => $urlGenerator->generate('dboard.auth.challenges'),
    'tokenUrl' => $urlGenerator->generate('dboard.auth.token'),
]);

?>
<div class="flex min-h-screen bg-secondary-50 dark:bg-primary-950">
    <div class="relative hidden w-1/2 overflow-hidden bg-primary-950 lg:flex lg:flex-col lg:justify-between lg:p-12">
        <div class="bg-aurora absolute inset-0" aria-hidden="true"></div>
        <div class="bg-grid absolute inset-0" aria-hidden="true"></div>
        <div class="relative flex items-center gap-3">
            <div class="flex size-9 items-center justify-center rounded-lg bg-white/10 text-white">
                <?php echo Bleet::svg()->logo('blackcube')->addClass('size-5'); ?>
            </div>
            <span class="text-xl font-semibold tracking-tight text-white">Dboard</span>
        </div>
        <div class="relative max-w-md">
            <p class="font-mono text-2xs font-medium uppercase tracking-label text-accent-300"><?php echo Html::encode($translator->translate('Administration', category: 'dboard-modules')); ?></p>
            <p class="mt-4 text-4xl font-semibold tracking-tight text-balance text-white"><?php echo Html::encode($translator->translate('Your site, in your hands.', category: 'dboard-modules')); ?></p>
            <p class="mt-4 text-base leading-relaxed text-secondary-400"><?php echo Html::encode($translator->translate('Contents, structure, languages and access rights: everything that makes your site, in one place.', category: 'dboard-modules')); ?></p>
        </div>
        <p class="relative font-mono text-xs text-secondary-500">&copy; Blackcube</p>
    </div>
    <div class="flex flex-1 items-center justify-center px-6 py-12 sm:px-12">
        <div class="w-full max-w-sm">
            <div class="mb-10 flex items-center gap-2.5 lg:hidden">
                <div class="flex size-8 items-center justify-center rounded-lg bg-primary-950 text-white dark:bg-white/10">
                    <?php echo Bleet::svg()->logo('blackcube')->addClass('size-4'); ?>
                </div>
                <span class="text-lg font-semibold tracking-tight text-secondary-950 dark:text-white">Dboard</span>
            </div>
            <h1 class="text-2xl font-semibold tracking-tight text-secondary-950 dark:text-white"><?php echo Html::encode($translator->translate('Sign in', category: 'dboard-modules')); ?></h1>
            <p class="mt-2 text-sm text-secondary-500 dark:text-secondary-400"><?php echo Html::encode($translator->translate('Use your passkey or your password.', category: 'dboard-modules')); ?></p>
            <div class="mt-8 space-y-6">
                <div class="space-y-6" data-login-device="block">
                    <?php echo Bleet::button($translator->translate('Sign in with a passkey', category: 'dboard-modules'))
                        ->icon('finger-print')
                        ->ghost()
                        ->secondary()
                        ->addButtonClass('w-full', 'justify-center', 'bg-white', 'dark:bg-white/5')
                        ->attribute('dboard-login-device', $loginDeviceAttributes); ?>
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="w-full border-t border-secondary-200 dark:border-white/10"></div>
                        </div>
                        <div class="relative flex justify-center">
                            <span class="bg-secondary-50 px-3 text-xs text-secondary-500 dark:bg-primary-950 dark:text-secondary-400"><?php echo Html::encode($translator->translate('or', category: 'dboard-modules')); ?></span>
                        </div>
                    </div>
                </div>
                <?php echo Html::form()
                    ->post($urlGenerator->generate('dboard.login', [], $loginQuery))
                    ->csrf($csrf)
                    ->noValidate()
                    ->addClass('space-y-5')
                    ->open(); ?>
                    <?php echo Bleet::input($model, 'email')->email(); ?>
                    <?php echo Bleet::input($model, 'password')->password()->showable(); ?>
                    <?php echo Bleet::checkbox($model, 'rememberMe'); ?>
                    <div class="pt-1">
                        <?php echo Bleet::submitButton($translator->translate('Sign in', category: 'dboard-modules'))
                            ->accent()
                            ->addButtonClass('w-full', 'justify-center'); ?>
                    </div>
                <?php echo Html::form()->close(); ?>
            </div>
        </div>
    </div>
</div>
