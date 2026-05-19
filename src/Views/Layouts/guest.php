<?php

declare(strict_types=1);

/**
 * guest.php
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
use Blackcube\Bleet\Bleet;
use Blackcube\Dboard\Assets\FaviconAsset;

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
        <?php $this->head() ?>
    </head>
    <body class="min-h-screen">
    <?php $this->beginBody() ?>

    <?php echo $content ?>

    <?php echo Bleet::toaster()->render() ?>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>
