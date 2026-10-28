<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\HostForm;
use Blackcube\Bleet\Bleet;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var Administrator $administrator
 * @var HostForm $formModel
 * @var CurrentRoute $currentRoute
 * @var bool $isProtected
 */

$isProtected = $isProtected ?? false;

?>

<div class="dboard-surface p-6">
    <div class="basis-full mb-4">
        <?php
        $toggle = Bleet::toggle($formModel, 'active')
                ->secondary();
        if ($isProtected === true):
            $toggle = $toggle->disabled(true);
        endif;
        echo $toggle->render();
        ?>
    </div>

    <div class="flex gap-4">
        <div class="basis-full">
            <?php
            $nameInput = Bleet::input($formModel, 'name')
                    ->placeholder('ex: localhost, blackcube.io')
                    ->secondary();
            if ($isProtected === true):
                $nameInput = $nameInput->disabled(true);
            endif;
            echo $nameInput->render();
            ?>
        </div>
    </div>

    <div class="flex gap-4 mt-4">
        <div class="basis-1/2 grow">
            <?php echo Bleet::input($formModel, 'siteName')
                    ->secondary()
                    ->render();
            ?>
        </div>
        <div class="basis-1/2 grow">
            <?php echo Bleet::input($formModel, 'siteAlternateName')
                    ->secondary()
                    ->render();
            ?>
        </div>
    </div>

    <div class="flex gap-4 mt-4">
        <div class="basis-full">
            <?php echo Bleet::textarea($formModel, 'siteDescription')
                    ->rows(3)
                    ->secondary()
                    ->render();
            ?>
        </div>
    </div>
</div>