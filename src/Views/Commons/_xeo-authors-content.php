<?php

declare(strict_types=1);

/**
 * _xeo-authors-content.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Forms\XeoAuthorForm;
use Blackcube\Dcore\Models\Author;
use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var TranslatorInterface $translator
 * @var XeoAuthorForm[] $xeoAuthorForms
 * @var Author[] $availableAuthors
 * @var string $authorsRefreshUrl
 */

$xeoAuthorForms = $xeoAuthorForms ?? [];
$availableAuthors = $availableAuthors ?? [];

$authorOptions = [];
foreach ($availableAuthors as $author) {
    $authorOptions[$author->getId()] = $author->getFirstname() . ' ' . $author->getLastname();
}
$xeoAuthorFormHelper = new XeoAuthorForm();
?>

<?php if (!empty($authorOptions)): ?>
    <div class="flex gap-2 mb-4">
        <?php echo Bleet::select()
            ->active($xeoAuthorFormHelper, '[]authorId')
            ->options($authorOptions)
            ->placeholder($translator->translate('-- Choose an author --', category: 'dboard-content'))
            ->fieldData(['authors' => 'select'])
            ->secondary()
            ->wrapperAddClass('flex-1')
            ->render(); ?>
        <?php echo Bleet::button()
            ->icon('plus')
            ->outline()
            ->xs()
            ->primary()
            ->fieldData(['authors' => 'add'])
            ->render(); ?>
    </div>
<?php endif; ?>

<?php if (!empty($xeoAuthorForms)): ?>
    <?php echo Html::openTag('ul', ['class' => 'space-y-2']); ?>
        <?php foreach ($xeoAuthorForms as $index => $form): ?>
            <?php echo Html::openTag('li', [
                'data-authors' => 'author',
                'draggable' => 'false',
                'class' => 'flex items-center gap-2 p-2 bg-secondary-50 rounded-lg',
            ]); ?>
                <?php echo Html::tag('span', Bleet::svg()->outline('arrows-up-down')->addClass('size-5', 'text-secondary-400'))
                    ->attribute('data-authors', 'handle')
                    ->encode(false); ?>
                <?php echo Bleet::input()
                    ->hidden()
                    ->active($form, '[]authorId')
                    ->fieldData(['authors' => 'id'])
                    ->render(); ?>
                <span class="flex-1 text-sm text-secondary-700"><?php echo Html::encode($form->getAuthorDisplayName()); ?></span>
                <?php echo Bleet::button()
                    ->icon('x-mark')
                    ->outline()
                    ->xs()
                    ->danger()
                    ->fieldData(['authors' => 'remove'])
                    ->render(); ?>
            <?php echo Html::closeTag('li'); ?>
        <?php endforeach; ?>
    <?php echo Html::closeTag('ul'); ?>
<?php elseif (empty($authorOptions)): ?>
    <p class="text-sm text-secondary-500 italic"><?php echo $translator->translate('No author available.', category: 'dboard-content'); ?></p>
<?php endif; ?>
