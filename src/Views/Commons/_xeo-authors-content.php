<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Models\Forms\XeoAuthorForm;
use Blackcube\Dcore\Models\Author;
use Blackcube\Bleet\Bleet;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
use Yiisoft\Html\Html;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var TranslatorInterface $translator
 * @var XeoAuthorForm[] $xeoAuthorForms
 * @var ActiveQueryInterface|null $availableAuthorsQuery
 * @var string $authorsRefreshUrl
 */

$xeoAuthorForms = $xeoAuthorForms ?? [];
$availableAuthorsQuery = $availableAuthorsQuery ?? null;

$authorOptions = [];
if ($availableAuthorsQuery !== null):
    /** @var Author $author */
    foreach ($availableAuthorsQuery->each() as $author):
        $authorOptions[$author->getId()] = $author->getFirstname() . ' ' . $author->getLastname();
    endforeach;
endif;
$xeoAuthorFormHelper = new XeoAuthorForm();
?>

<?php if (empty($authorOptions) === false): ?>
    <div class="flex items-center gap-2 mb-4">
        <?php echo Bleet::select($xeoAuthorFormHelper, '[]authorId')
            ->optionsData($authorOptions)
            ->prompt($translator->translate('-- Choose an author --', category: 'dboard-content'))
            ->fieldData([
                'authors' => 'select',
            ])
            ->secondary()
            ->addContainerClass('flex-1')
            ->render(); ?>
        <?php echo Bleet::button()
            ->icon('plus')
            ->outline()
            ->xs()
            ->primary()
            ->fieldData([
                'authors' => 'add',
            ])
            ->render(); ?>
    </div>
<?php endif; ?>

<?php if (empty($xeoAuthorForms) === false): ?>
    <?php echo Html::openTag('ul', [
        'class' => 'space-y-2',
    ]); ?>
        <?php foreach ($xeoAuthorForms as $index => $form): ?>
            <?php echo Html::openTag('li', [
                'data-authors' => 'author',
                'draggable' => 'false',
                'class' => 'flex items-center gap-2 p-2 bg-secondary-50 rounded-lg',
            ]); ?>
                <?php echo Html::tag('span', Bleet::svg()->outline('arrows-up-down')->addClass('size-5', 'text-secondary-400'))
                    ->attribute('data-authors', 'handle')
                    ->encode(false); ?>
                <?php echo Bleet::input($form, '[]authorId')
                    ->hidden()
                    ->fieldData([
                        'authors' => 'id',
                    ])
                    ->render(); ?>
                <span class="flex-1 text-sm text-secondary-700"><?php echo Html::encode($form->getAuthorDisplayName()); ?></span>
                <?php echo Bleet::button()
                    ->icon('x-mark')
                    ->outline()
                    ->xs()
                    ->danger()
                    ->fieldData([
                        'authors' => 'remove',
                    ])
                    ->render(); ?>
            <?php echo Html::closeTag('li'); ?>
        <?php endforeach; ?>
    <?php echo Html::closeTag('ul'); ?>
<?php elseif (empty($authorOptions) === true): ?>
    <p class="text-sm text-secondary-500 italic"><?php echo $translator->translate('No author available.', category: 'dboard-content'); ?></p>
<?php endif; ?>
