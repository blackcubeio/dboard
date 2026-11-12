<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Widgets\Widgets;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var array<int, array{id: string, name: string, version: ?string, active: bool, pending: bool, configureRoute: ?string, orphan: bool}> $addons
 */

?>
        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <?php echo Widgets::pageHeading($translator->translate('Addons', category: 'dboard-modules'))->render(); ?>

            <div class="dboard-surface p-4">
                <bleet-ajaxify id="addons-list">
                    <?php echo $this->render('Addons/_list', [
                        'addons' => $addons,
                        'urlGenerator' => $urlGenerator,
                    ]); ?>
                </bleet-ajaxify>
            </div>
        </main>
