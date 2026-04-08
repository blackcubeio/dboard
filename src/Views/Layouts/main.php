<?php

declare(strict_types=1);

/**
 * main.php
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
 * @var Yiisoft\Translator\TranslatorInterface $translator
 * @var Blackcube\Dboard\Models\Administrator|null $administrator
 */

use Blackcube\Dboard\Assets\AppAsset;
use Blackcube\Dboard\Assets\FaviconAsset;
use Blackcube\Dboard\Assets\StaticAsset;
use Blackcube\Dboard\Widgets\Widgets;
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
        <body class="min-h-screen bg-secondary-50">
        <?php $this->beginBody() ?>
        <div class="flex min-h-screen">
            <?php echo Widgets::sidebar()
                    ->user($administrator)
                    ->translator($translator)
                    ->secondary()
                    ->currentRoute($currentRoute->getName() ?? '')
                    ->render();
            ?>

            <div class="flex-1 flex flex-col lg:pl-64">
                <?php echo Bleet::header()
                        // ->search($urlGenerator->generate('dboard.search'), 'Rechercher...')
                        ->addWidget(Widgets::preview()->user($administrator)->translator($translator)->render())
                        ->addSeparator()
                        ->profile(
                                Bleet::profile()
                                        ->avatar($administrator->getName())
                                        ->name($administrator->getName())
                                        ->items([
                                                Bleet::a()->url($urlGenerator->generate('dboard.account', ['id' => $administrator->getId()]))->content($translator->translate('My account', category: 'dboard-modules')),
                                                Bleet::a()->url($urlGenerator->generate('dboard.logout'))->content($translator->translate('Log out', category: 'dboard-modules')),
                                        ])
                        )
                        ->secondary()
                        ->render();
                ?>
            <?php echo $content ?>
                <?php echo Bleet::footer()
                        ->copyright('Blackcube')
                        ->version('4.0.0')
                        ->secondary()
                        ->render();
                ?>
            </div>
        </div>
        <?php echo Bleet::toaster()->render() ?>
            <?php echo Bleet::overlay() ?>
            <?php echo Bleet::modal()->render(); ?>
            <?php echo Bleet::drawer()->render(); ?>
        <?php $this->endBody() ?>
        </body>
    </html>
<?php $this->endPage() ?>
