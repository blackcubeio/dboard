<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Widgets\Widgets;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var bool $isInSync
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var CurrentRoute $currentRoute
 */

?>
        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <?php echo Widgets::pageHeading($translator->translate('RBAC', category: 'dboard-modules'))->render(); ?>

            <div class="dboard-surface p-4">
                <bleet-ajaxify id="rbac-status">
                    <?php echo $this->render('Rbac/_status', [
                        'isInSync' => $isInSync,
                        'urlGenerator' => $urlGenerator,
                        'currentRoute' => $currentRoute,
                    ]); ?>
                </bleet-ajaxify>
            </div>
        </main>
