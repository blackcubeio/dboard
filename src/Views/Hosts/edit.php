<?php

declare(strict_types=1);

/**
 * edit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Host;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\HostForm;
use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var Administrator $administrator
 * @var HostForm $formModel
 * @var CurrentRoute $currentRoute
 * @var Host $host
 * @var string|null $csrf
 */

$isProtected = $host->getId() === 1;

?>
<main class="flex-1 p-4 sm:p-6 lg:p-8">
    <?php echo Html::form()
            ->post($urlGenerator->generate('dboard.hosts.edit', ['id' => $host->getId()]))
            ->csrf($csrf)
            ->noValidate()
            ->open(); ?>
    <?php echo Bleet::cardHeader()
            ->left(Bleet::a()->url($urlGenerator->generate('dboard.hosts'))->icon('chevron-left')->outline()->addClass('text-white', 'hover:text-white', '[&>svg]:size-6'))
            ->title($translator->translate('Host', category: 'dboard-modules'))
            ->primary();
    ?>

    <?php echo $this->render('Hosts/_form', [
            'urlGenerator' => $urlGenerator,
            'administrator' => $administrator,
            'formModel' => $formModel,
            'currentRoute' => $currentRoute,
            'isProtected' => $isProtected,
    ]); ?>

    <div class="flex justify-end gap-4 mt-6">
        <?php echo Bleet::a($translator->translate('Cancel', category: 'dboard-common'))
                ->url($urlGenerator->generate('dboard.hosts'))
                ->icon('x-mark')
                ->ghost()
                ->secondary()
                ->render();
        ?>
        <?php echo Bleet::button($translator->translate('Save', category: 'dboard-common'))
                ->icon('check')
                ->submit()
                ->primary()
                ->render();
        ?>
    </div>
    <?php echo Html::closeTag('form'); ?>
</main>