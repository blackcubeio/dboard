<?php

declare(strict_types=1);

/**
 * organization-index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\GlobalXeo;
use Blackcube\Dcore\Models\Host;
use Blackcube\Bleet\Bleet;
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
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <?php echo Bleet::cardHeader()
                ->icon('globe-alt')
                ->title($kindLabel)
                ->primary();
            ?>

            <div class="bg-white rounded-b-lg shadow-lg p-4">
                <bleet-ajaxify id="xeo-organization-list">
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
