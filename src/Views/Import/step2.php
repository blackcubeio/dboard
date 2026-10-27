<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Blackcube\Dboard\Models\Forms\ImportExistenceForm;
use Blackcube\Dboard\Widgets\Widgets;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var array $importedElement
 * @var string $elementType
 * @var array $existence
 * @var bool $exists
 * @var array $treeTargets
 * @var ImportExistenceForm $formModel
 * @var string|null $csrf
 */

$typeLabel = $elementType === 'content' ? $translator->translate('Content', category: 'dboard-modules') : $translator->translate('Tag', category: 'dboard-modules');
$name = $importedElement['name'] ?? $translator->translate('(no name)', category: 'dboard-modules');
$slugPath = ($importedElement['slug'] !== null) ? ($importedElement['slug']['path'] ?? $translator->translate('(no slug)', category: 'dboard-modules')) : $translator->translate('(no slug)', category: 'dboard-modules');
$treePath = $importedElement['path'] ?? $translator->translate('(no path)', category: 'dboard-modules');

$treeOptions = [];
foreach ($treeTargets as $target):
    $treeOptions[$target['path']] = str_repeat('— ', $target['level'] - 1) . $target['name'];
endforeach;

?>
<main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
    <?php echo Widgets::pageHeading($translator->translate('Existence check', category: 'dboard-modules'))->render(); ?>

    <div class="mb-8">
        <?php echo Bleet::step()
            ->current(1)
            ->addStep('Upload', $urlGenerator->generate('dboard.import.step1'))
            ->addStep('Existence', $urlGenerator->generate('dboard.import.step2'))
            ->addStep($translator->translate('References', category: 'dboard-modules'))
            ->addStep($translator->translate('Import', category: 'dboard-modules'))
            ->render();
        ?>
    </div>

    <div class="dboard-surface p-6">
        <div class="mb-6 rounded-lg border border-secondary-200 p-4 dark:border-white/10">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="font-medium text-secondary-500 dark:text-secondary-400"><?php echo $translator->translate('Type', category: 'dboard-modules'); ?></dt>
                    <dd class="mt-1 text-secondary-900 dark:text-white"><?php echo Html::encode($typeLabel); ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-secondary-500 dark:text-secondary-400"><?php echo $translator->translate('Name', category: 'dboard-common'); ?></dt>
                    <dd class="mt-1 text-secondary-900 dark:text-white"><?php echo Html::encode($name); ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-secondary-500 dark:text-secondary-400">Slug</dt>
                    <dd class="mt-1 text-secondary-900 dark:text-white"><?php echo Html::encode($slugPath); ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-secondary-500 dark:text-secondary-400"><?php echo $translator->translate('Tree path', category: 'dboard-modules'); ?></dt>
                    <dd class="mt-1 text-secondary-900 dark:text-white"><?php echo Html::encode($treePath); ?></dd>
                </div>
            </dl>
        </div>

        <?php if ($exists === true): ?>
            <div class="mb-6">
                <?php echo Bleet::alert()
                    ->content($translator->translate('An element with the same identifier or slug already exists.', category: 'dboard-modules'))
                    ->warning()
                    ->render();
                ?>
            </div>

            <?php echo Bleet::hr($translator->translate('Overwrite existing', category: 'dboard-modules'))->warning(); ?>
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.import.step2'))
                ->csrf($csrf)
                ->open();
            ?>
                <?php echo Bleet::input($formModel, 'mode')->hidden()->value('overwrite')->render(); ?>
                <div class="flex justify-between items-center">
                    <?php echo Bleet::a($translator->translate('Previous', category: 'dboard-common'))
                        ->url($urlGenerator->generate('dboard.import.step1'))
                        ->icon('arrow-left')
                        ->ghost()
                        ->secondary()
                        ->render();
                    ?>
                    <?php echo Bleet::submitButton($translator->translate('{type, select, content{Overwrite existing content} other{Overwrite existing tag}}', [
                        'type' => $elementType,
                    ], 'dboard-modules'))
                        ->icon('exclamation-triangle')
                        ->warning()
                        ->render();
                    ?>
                </div>
            <?php echo Html::form()->close(); ?>

            <?php echo Bleet::hr($translator->translate('Create new', category: 'dboard-modules'))->primary(); ?>
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.import.step2'))
                ->csrf($csrf)
                ->open();
            ?>
                <?php echo Bleet::input($formModel, 'mode')->hidden()->value('create')->render(); ?>
                <div class="mb-4">
                    <?php echo Bleet::select($formModel, 'targetPath')
                        ->optionsData($treeOptions)
                        ->secondary()
                        ->render();
                    ?>
                </div>
                <div class="flex justify-between items-center">
                    <?php echo Bleet::a($translator->translate('Previous', category: 'dboard-common'))
                        ->url($urlGenerator->generate('dboard.import.step1'))
                        ->icon('arrow-left')
                        ->ghost()
                        ->secondary()
                        ->render();
                    ?>
                    <?php echo Bleet::submitButton($translator->translate('{type, select, content{Create new content} other{Create new tag}}', [
                        'type' => $elementType,
                    ], 'dboard-modules'))
                        ->icon('plus')
                        ->accent()
                        ->render();
                    ?>
                </div>
            <?php echo Html::form()->close(); ?>

        <?php else: ?>
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.import.step2'))
                ->csrf($csrf)
                ->open();
            ?>
                <?php echo Bleet::input($formModel, 'mode')->hidden()->value('create')->render(); ?>
                <div>
                    <?php echo Bleet::select($formModel, 'targetPath')
                        ->optionsData($treeOptions)
                        ->secondary()
                        ->render();
                    ?>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <?php echo Bleet::submitButton($translator->translate('Continue', category: 'dboard-common'))
                        ->icon('arrow-right')
                        ->accent()
                        ->render();
                    ?>
                </div>
            <?php echo Html::form()->close(); ?>
        <?php endif; ?>
    </div>
</main>
