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
        <?php $this->head(); ?>
    </head>
    <body class="min-h-screen bg-secondary-50 text-secondary-900 dark:bg-primary-950 dark:text-secondary-100">
    <?php $this->beginBody(); ?>

    <?php echo $content ?>

    <?php echo Bleet::toaster()->closeLabel($translator->translate('Close', category: 'dboard-common'))->render(); ?>
    <?php $this->endBody(); ?>
    </body>
    </html>
<?php $this->endPage(); ?>
