<?php

declare(strict_types=1);

/**
 * login.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Assets\StaticAsset;
use Blackcube\Dboard\Models\Forms\LoginForm;
use Blackcube\Bleet\Aurelia;
use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var LoginForm $model
 * @var string|null $csrf
 */

$assetManager->register(StaticAsset::class);
$staticBundle = $assetManager->getBundle(StaticAsset::class);
$backgroundUrl = $staticBundle->baseUrl . '/login.jpg';

?>
<?php echo Html::openTag('div', [
    'class' => 'min-h-screen flex items-center justify-center bg-cover bg-center bg-no-repeat',
    'style' => "background-image: url('$backgroundUrl');",
]);
?>
    <div class="w-full max-w-md">
        <?php $card = Bleet::card()
                ->encode(false)
                ->addClass('rounded-2xl', 'shadow-2xl');
        ?>
        <?php $card = $card->beginHeader(); ?>
            <div class="px-4 py-5 sm:px-6">
                <div class="flex flex-col items-center">
                    <div class="bg-black rounded-xl p-0 text-white">
                        <?php echo Bleet::svg()
                                ->logo('blackcube')
                                ->addClass('size-20');
                        ?>
                    </div>
                </div>
            </div>
        <?php $card = $card->endHeader(); ?>
        <?php $card = $card->beginContent(); ?>
            <?php echo Html::form()
                    ->post($urlGenerator->generate('dboard.login'))
                    ->csrf($csrf)
                    ->noValidate()
                    ->addClass('space-y-6')
                    ->open();
            ?>
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
                    <?php echo Bleet::checkbox()
                        ->active($model, 'rememberMe')
                        ->value('1')
                        ->render();
                    ?>
                </div>
                <div class="space-y-3">
                    <?php echo Bleet::button($translator->translate('Sign in', category: 'dboard-modules'))
                        ->submit()
                        ->primary()
                        ->addClass('w-full')
                        ->render();
                    ?>
                    <?php
                    $loginAttr = Aurelia::attributesCustomAttribute([
                        'challengeUrl' => $urlGenerator->generate('dboard.auth.challenges'),
                        'tokenUrl' => $urlGenerator->generate('dboard.auth.token'),
                    ]);
                    ?>
                    <?php echo Bleet::button()
                        ->button()
                        ->icon('finger-print')
                        ->accent()
                        ->addClass('w-full', 'justify-items-center')
                        ->attribute('dboard-login-device', $loginAttr)
                        ->render();
                    ?>
                </div>
            <?php echo Html::form()
                    ->close();
            ?>
        <?php $card = $card->endContent(); ?>
        <?php echo $card->render(); ?>
    </div>
<?php echo Html::closeTag('div');
?>
