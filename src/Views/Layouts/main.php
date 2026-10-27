<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

/**
 * @var Yiisoft\Aliases\Aliases $aliases
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $content
 * @var string|null $csrf
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Router\CurrentRoute $currentRoute
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Translator\TranslatorInterface $translator
 * @var Blackcube\Dboard\Models\Administrator|null $administrator
 */

use Blackcube\Dboard\Assets\AppAsset;
use Blackcube\Dboard\Assets\FaviconAsset;
use Blackcube\Dboard\Assets\StaticAsset;
use Blackcube\Dboard\Widgets\Widgets;
use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;

$assetManager->register(AppAsset::class);
$assetManager->register(StaticAsset::class);
$assetManager->register(FaviconAsset::class);
$this->addCssFiles($assetManager->getCssFiles());
$this->addCssStrings($assetManager->getCssStrings());
$this->addJsFiles($assetManager->getJsFiles());
$this->addJsStrings($assetManager->getJsStrings());
$this->addJsVars($assetManager->getJsVars());
$bundle = $assetManager->getBundle(AppAsset::class);
$this->registerJsVar('webpackBaseUrl', $bundle->baseUrl . '/');

$this->beginPage()
?>
    <!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Blackcube</title>
            <base href="/">
            <?php $this->head(); ?>
        </head>
        <body class="min-h-screen bg-secondary-50 text-secondary-900 dark:bg-primary-950 dark:text-secondary-100">
        <?php $this->beginBody(); ?>
        <?php echo Widgets::sidebar()
                ->user($administrator)
                ->translator($translator)
                ->preview(Widgets::preview()->user($administrator)->translator($translator)->render())
                ->secondary()
                ->currentRoute($currentRoute->getName() ?? '')
                ->render();
        ?>

        <div class="flex min-h-screen flex-col lg:pl-64">
            <div class="sticky top-0 z-30 flex h-14 shrink-0 items-center gap-3 border-b border-secondary-200 bg-white/80 px-4 backdrop-blur-md lg:hidden dark:border-white/10 dark:bg-primary-950/80">
                <?php echo Html::openTag('button', array_merge([
                    'type' => 'button',
                    'class' => '-m-2 cursor-pointer rounded-md p-2 text-secondary-600 transition-colors hover:bg-secondary-100 focus-visible:outline-2 focus-visible:outline-accent-500 dark:text-secondary-300 dark:hover:bg-white/10',
                ], Bleet::burger())); ?>
                    <span class="sr-only"><?php echo Html::encode($translator->translate('Open menu', category: 'dboard-common')); ?></span>
                    <?php echo Bleet::svg()->outline('bars-3')->addClass('size-6'); ?>
                <?php echo Html::closeTag('button'); ?>
                <span class="flex items-center gap-2">
                    <span class="flex size-6 items-center justify-center rounded-md bg-primary-950 text-white dark:bg-white/10">
                        <?php echo Bleet::svg()->logo('blackcube')->addClass('size-3.5'); ?>
                    </span>
                    <span class="text-sm font-semibold tracking-tight text-secondary-900 dark:text-white">Dboard</span>
                </span>
                <span class="flex-1"></span>
                <?php echo Html::openTag('button', [
                    'type' => 'button',
                    'dboard-dark-toggle' => '',
                    'title' => $translator->translate('Switch between light and dark', category: 'dboard-common'),
                    'aria-label' => $translator->translate('Switch between light and dark', category: 'dboard-common'),
                    'class' => 'inline-flex size-9 cursor-pointer items-center justify-center rounded-md text-secondary-500 transition-colors hover:bg-secondary-100 focus-visible:outline-2 focus-visible:outline-accent-500 dark:text-secondary-400 dark:hover:bg-white/10',
                ]); ?>
                    <?php echo Bleet::svg()->outline('moon')->addClass('size-4', 'dark:hidden'); ?>
                    <?php echo Bleet::svg()->outline('sun')->addClass('hidden', 'size-4', 'dark:block'); ?>
                <?php echo Html::closeTag('button'); ?>
            </div>

            <div class="canvas-glow flex flex-1 flex-col">
                <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col">
                    <?php echo $content; ?>
                </div>
            </div>
        </div>
        <?php echo Bleet::toaster()->closeLabel($translator->translate('Close', category: 'dboard-common'))->render(); ?>
            <?php echo Bleet::overlay(); ?>
            <?php echo Bleet::modal()
                    ->loadError($translator->translate('Failed to load the content.', category: 'dboard-common'))
                    ->submitError($translator->translate('Failed to send the form.', category: 'dboard-common'))
                    ->render();
            ?>
            <?php echo Bleet::drawer()
                    ->loadError($translator->translate('Failed to load the content.', category: 'dboard-common'))
                    ->submitError($translator->translate('Failed to send the form.', category: 'dboard-common'))
                    ->render();
            ?>
        <?php $this->endBody(); ?>
        </body>
    </html>
<?php $this->endPage(); ?>
