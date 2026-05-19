<?php

declare(strict_types=1);

/**
 * onboarding.php
 *
 * @copyright 2010-2026 Blackcube
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
 */

use Blackcube\Dboard\Assets\AppAsset;
use Blackcube\Dboard\Assets\FaviconAsset;
use Blackcube\Dboard\Assets\StaticAsset;
use Blackcube\Bleet\Bleet;

$assetManager->register(AppAsset::class);
$assetManager->register(StaticAsset::class);
$assetManager->register(FaviconAsset::class);
$this->addCssFiles($assetManager->getCssFiles());
$this->addCssStrings($assetManager->getCssStrings());
$this->addJsFiles($assetManager->getJsFiles());
$this->addJsStrings($assetManager->getJsStrings());
$this->addJsVars($assetManager->getJsVars());
$bundle = $assetManager->getBundle(AppAsset::class);
$staticBundle = $assetManager->getBundle(StaticAsset::class);
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
        <?php $this->head() ?>
    </head>
    <body class="min-h-screen bg-cover bg-center bg-no-repeat" style="background-image: url('<?php echo $staticBundle->baseUrl ?>/montagne.jpg');">
    <?php $this->beginBody() ?>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full bg-secondary-50/60 rounded-2xl p-6">
            <?php echo $content ?>
        </div>
    </div>

    <?php echo Bleet::toaster()->render() ?>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>
