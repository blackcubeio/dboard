<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Assets\StaticAsset;
use Blackcube\Dboard\Models\Forms\LoginForm;
use Blackcube\Html\Aurelia;
use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var LoginForm $model
 * @var string|null $returnUrl
 * @var string|null $csrf
 */

$assetManager->register(StaticAsset::class);
$staticBundle = $assetManager->getBundle(StaticAsset::class);
$backgroundUrl = $staticBundle->baseUrl . '/login.jpg';
$loginQuery = [];
if ($returnUrl !== null):
    $loginQuery['redirect'] = $returnUrl;
endif;

?>
<?php echo Html::openTag('div', [
    'class' => 'min-h-screen flex items-center justify-center bg-cover bg-center bg-no-repeat',
    'style' => 'background-image: url(\'' . $backgroundUrl . '\');',
]);
?>
    <div class="w-full sm:mx-auto sm:max-w-[480px]">
        <div class="flex flex-col items-center">
            <div class="bg-black rounded-xl p-0 text-white">
                <?php echo Bleet::svg()
                        ->logo('blackcube')
                        ->addClass('size-20');
                ?>
            </div>
        </div>
        <div class="mt-10 bg-white px-6 py-12 shadow-sm sm:rounded-lg sm:px-12">
            <?php echo Html::form()
                    ->post($urlGenerator->generate('dboard.login', [], $loginQuery))
                    ->csrf($csrf)
                    ->noValidate()
                    ->addClass('space-y-6')
                    ->open();
            ?>
                <?php echo Bleet::input($model, 'email')
                        ->email();
                ?>
                <?php echo Bleet::input($model, 'password')
                        ->password()
                        ->showable();
                ?>
                <?php echo Bleet::checkbox($model, 'rememberMe'); ?>
                <div class="space-y-3">
                    <?php echo Bleet::submitButton($translator->translate('Sign in', category: 'dboard-modules'))
                        ->buttonClass(
                            'flex', 'w-full', 'justify-center', 'rounded-md', 'bg-primary-600',
                            'px-3', 'py-1.5', 'text-sm/6', 'font-semibold', 'text-white', 'shadow-xs',
                            'hover:bg-primary-500', 'cursor-pointer',
                            'focus-visible:outline-2', 'focus-visible:outline-offset-2', 'focus-visible:outline-primary-600',
                        );
                    ?>
                    <?php
                    $loginAttr = Aurelia::attributesCustomAttribute([
                        'challengeUrl' => $urlGenerator->generate('dboard.auth.challenges'),
                        'tokenUrl' => $urlGenerator->generate('dboard.auth.token'),
                    ]);
                    ?>
                    <?php echo Bleet::button()
                        ->icon('finger-print')
                        ->buttonClass(
                            'flex', 'w-full', 'justify-center', 'rounded-md', 'bg-accent-600',
                            'px-3', 'py-1.5', 'text-sm/6', 'font-semibold', 'text-white', 'shadow-xs',
                            'hover:bg-accent-500', 'cursor-pointer',
                            'focus-visible:outline-2', 'focus-visible:outline-offset-2', 'focus-visible:outline-accent-600',
                        )
                        ->attribute('dboard-login-device', $loginAttr);
                    ?>
                </div>
            <?php echo Html::form()
                    ->close();
            ?>
        </div>
    </div>
<?php echo Html::closeTag('div');
?>
