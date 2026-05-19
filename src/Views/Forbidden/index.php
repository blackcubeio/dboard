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
use Yiisoft\Html\Html;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 */

$loginUrl = $urlGenerator->generate('dboard.login');
$title = $translator->translate('Access denied', category: 'dboard-common');
$message = $translator->translate('Your account does not have permission to access this section.', category: 'dboard-common');
$backLabel = $translator->translate('Back to login', category: 'dboard-common');
?>
<?php echo Html::openTag('div', ['class' => 'min-h-screen flex items-center justify-center px-4']); ?>
    <div class="w-full max-w-md text-center">
        <div class="flex justify-center mb-6">
            <?php echo Bleet::svg()->logo('blackcube')->addClass('size-16')->render(); ?>
        </div>
        <?php echo Html::tag('h1', Html::encode($title), ['class' => 'text-3xl font-bold text-gray-900 mb-3']); ?>
        <?php echo Html::tag('p', Html::encode($message), ['class' => 'text-base text-gray-600 mb-8']); ?>
        <?php echo Html::a(
            Html::encode($backLabel),
            $loginUrl,
            ['class' => 'inline-flex items-center px-5 py-2.5 rounded-lg bg-primary-600 text-white font-medium hover:bg-primary-700 transition-colors cursor-pointer'],
        ); ?>
    </div>
<?php echo Html::closeTag('div'); ?>
