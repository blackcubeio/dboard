<?php

declare(strict_types=1);

/**
 * _passkeys-content.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Passkey;
use Blackcube\Bleet\Aurelia;
use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var TranslatorInterface $translator
 * @var Administrator $administrator
 * @var Passkey[] $passkeys
 * @var UrlGeneratorInterface $urlGenerator
 * @var CurrentRoute $currentRoute
 */

$challengeUrl = $urlGenerator->generate('dboard.auth.challenges');
$registerUrl = $urlGenerator->generate('dboard.account.passkeys', ['id' => $administrator->getId()]);
?>
<div class="p-6">
    <div class="mb-4">
        <?php echo Bleet::h3($administrator->getName())->render(); ?>
        <?php echo Bleet::p($translator->translate('Manage your WebAuthn passkeys.', category: 'dboard-modules'))->secondary()->render(); ?>
    </div>

    <bleet-ajaxify id="passkeys-list">
        <?php echo $this->render('Account/_passkeys-list-content', [
            'administrator' => $administrator,
            'passkeys' => $passkeys,
            'urlGenerator' => $urlGenerator,
        ]); ?>
    </bleet-ajaxify>

    <div class="mt-4">
        <?php
        $attachAttr = Aurelia::attributesCustomAttribute([
            'challengeUrl' => $challengeUrl,
            'registerUrl' => $registerUrl,
            'errorTitle' => $translator->translate('Error', category: 'dboard-common'),
            'errorContent' => $translator->translate('Failed to add passkey.', category: 'dboard-common'),
        ]);
        ?>
        <?php echo Bleet::button($translator->translate('Add a passkey', category: 'dboard-modules'))
            ->icon('plus')
            ->primary()
            ->attribute('dboard-attach-device', $attachAttr)
            ->render();
        ?>
    </div>

    <div class="flex gap-4 pt-6 mt-6 border-t border-gray-200">
        <?php echo Bleet::button($translator->translate('Close', category: 'dboard-common'))
            ->secondary()
            ->attribute('data-drawer', 'close')
            ->render();
        ?>
    </div>
</div>
