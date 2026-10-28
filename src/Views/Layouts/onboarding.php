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
 */

use Blackcube\Dboard\Assets\AppAsset;
use Blackcube\Dboard\Assets\FaviconAsset;
use Blackcube\Bleet\Bleet;

$assetManager->register(AppAsset::class);
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
    <body class="dboard-canvas min-h-screen">
    <?php $this->beginBody(); ?>

    <div class="relative isolate flex min-h-screen items-center justify-center overflow-hidden bg-primary-950 px-4 py-12 sm:px-6 lg:px-8">
        <div class="absolute inset-0 -z-10 bg-aurora"></div>
        <div class="absolute inset-0 -z-10 bg-grid"></div>
        <div class="w-full max-w-2xl">
            <?php echo $content ?>
        </div>
    </div>

    <?php echo Bleet::toaster()->closeLabel($translator->translate('Close', category: 'dboard-common'))->render(); ?>
    <?php $this->endBody(); ?>
    </body>
    </html>
<?php $this->endPage(); ?>
