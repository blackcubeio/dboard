<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Assets\PasskeysAsset;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Passkey;
use Blackcube\Bleet\Bleet;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var TranslatorInterface $translator
 * @var Administrator $administrator
 * @var ActiveQueryInterface $passkeysQuery
 * @var UrlGeneratorInterface $urlGenerator
 * @var AssetManager $assetManager
 * @var string|null $csrf
 */

$deleteUrl = $urlGenerator->generate('dboard.account.passkeys', [
    'id' => $administrator->getId(),
]);
$assetManager->register(PasskeysAsset::class);
$passkeyIconsBundle = $assetManager->getBundle(PasskeysAsset::class);
?>
<?php if ($passkeysQuery->exists() === false): ?>
    <div class="text-center py-8">
        <?php echo Bleet::p($translator->translate('No passkey registered.', category: 'dboard-modules'))->secondary()->render(); ?>
    </div>
<?php else: ?>
    <div class="space-y-2">
        <?php /** @var Passkey $passkey */ ?>
        <?php foreach ($passkeysQuery->each() as $passkey): ?>
            <?php $device = $passkey->passkeyDevice; ?>
            <div class="flex items-center justify-between p-3 bg-white border border-secondary-200 rounded-lg">
                <div class="flex items-center gap-3">
                    <?php if ($device !== null && $device->isIconLight() === true): ?>
                        <?php echo Html::img($passkeyIconsBundle->baseUrl . '/' . $device->getAaguid() . '_light.svg', $device->getName(), [
                            'class' => 'size-5',
                        ]); ?>
                    <?php else: ?>
                        <?php echo Bleet::svg()->outline('finger-print')->addClass('size-5', 'text-secondary-500')->render(); ?>
                    <?php endif; ?>
                    <div>
                        <span class="text-sm font-medium text-secondary-900">
                            <?php echo Html::encode($device !== null ? $device->getName() : $passkey->getName()); ?>
                        </span>
                        <span class="block text-xs text-secondary-500">
                            <?php echo $translator->translate('Added on {date}', [
                                'date' => (new \IntlDateFormatter($translator->getLocale(), \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE))->format($passkey->getDateCreate()),
                            ], 'dboard-modules'); ?>
                        </span>
                    </div>
                </div>
                <?php echo Html::form()
                    ->method('DELETE')
                    ->action($deleteUrl)
                    ->csrf($csrf)
                    ->noValidate()
                    ->open(); ?>
                    <?php echo Html::hiddenInput('passkeyId', $passkey->getId()); ?>
                    <?php echo Bleet::submitButton()
                        ->icon('trash')
                        ->ghost()
                        ->danger()
                        ->xs()
                        ->render(); ?>
                <?php echo Html::closeTag('form'); ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
