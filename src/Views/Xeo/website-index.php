<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\GlobalXeo;
use Blackcube\Dcore\Models\Host;
use Blackcube\Dboard\Widgets\Widgets;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var array<int, array{host: Host, globalXeo: ?GlobalXeo}> $hostData
 * @var string $editRoute
 * @var string $toggleRoute
 * @var string $deleteRoute
 * @var string $kindLabel
 * @var CurrentRoute $currentRoute
 */

?>
        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <?php echo Widgets::pageHeading($kindLabel)->render(); ?>

            <div class="dboard-surface p-4">
                <bleet-ajaxify id="xeo-website-list">
                <?php
                    echo $this->render('Xeo/_list', [
                        'hostData' => $hostData,
                        'editRoute' => $editRoute,
                        'toggleRoute' => $toggleRoute,
                        'deleteRoute' => $deleteRoute,
                        'kindLabel' => $kindLabel,
                        'urlGenerator' => $urlGenerator,
                    ]);
                ?>
                </bleet-ajaxify>
            </div>
        </main>
