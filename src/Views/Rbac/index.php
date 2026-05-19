<?php

declare(strict_types=1);

/**
 * index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
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
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <?php echo Bleet::cardHeader()
                ->icon('shield-check')
                ->title($translator->translate('RBAC', category: 'dboard-modules'))
                ->primary();
            ?>

            <div class="bg-white rounded-b-lg shadow-lg p-6">
                <bleet-ajaxify id="rbac-status">
                    <?php echo $this->render('Rbac/_status', [
                        'isInSync' => $isInSync,
                        'urlGenerator' => $urlGenerator,
                        'currentRoute' => $currentRoute,
                    ]); ?>
                </bleet-ajaxify>
            </div>
        </main>
