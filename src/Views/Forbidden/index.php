<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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
<?php echo Html::openTag('div', [
    'class' => 'min-h-screen flex items-center justify-center px-4',
]); ?>
    <div class="w-full max-w-md text-center">
        <div class="mb-6 flex justify-center">
            <div class="rounded-xl bg-primary-950 p-2 text-white dark:bg-white/10">
                <?php echo Bleet::svg()->logo('blackcube')->addClass('size-12')->render(); ?>
            </div>
        </div>
        <?php echo Html::tag('h1', Html::encode($title), [
            'class' => 'mb-3 text-3xl font-semibold tracking-tight text-secondary-950 dark:text-white',
        ]); ?>
        <?php echo Html::tag('p', Html::encode($message), [
            'class' => 'mb-8 text-base text-secondary-500 dark:text-secondary-400',
        ]); ?>
        <?php echo Bleet::a($backLabel, $loginUrl)
            ->button()
            ->icon('arrow-left')
            ->accent()
            ->render();
        ?>
    </div>
<?php echo Html::closeTag('div'); ?>
