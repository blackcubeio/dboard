<?php

declare(strict_types=1);

/**
 * step2.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Blackcube\Dboard\Models\Forms\ImportExistenceForm;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var array $data
 * @var string $elementType
 * @var array $existence
 * @var bool $exists
 * @var array $treeTargets
 * @var ImportExistenceForm $formModel
 * @var string|null $csrf
 */

$typeLabel = $elementType === 'content' ? $translator->translate('Content', category: 'dboard-modules') : $translator->translate('Tag', category: 'dboard-modules');
$name = $data['name'] ?? $translator->translate('(no name)', category: 'dboard-modules');
$slugPath = ($data['slug'] !== null) ? ($data['slug']['path'] ?? $translator->translate('(no slug)', category: 'dboard-modules')) : $translator->translate('(no slug)', category: 'dboard-modules');
$treePath = $data['path'] ?? $translator->translate('(no path)', category: 'dboard-modules');

$treeOptions = [];
foreach ($treeTargets as $target) {
    $treeOptions[$target['path']] = str_repeat('— ', $target['level'] - 1) . $target['name'];
}

?>
<main class="flex-1 p-4 sm:p-6 lg:p-8">
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

    <?php echo Bleet::cardHeader()
        ->icon('arrow-up-tray')
        ->title($translator->translate('Existence check', category: 'dboard-modules'))
        ->primary();
    ?>

    <div class="bg-white rounded-b-lg shadow-lg p-8">
        <div class="mb-6 rounded-md border border-gray-200 p-4">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="font-medium text-gray-500"><?php echo $translator->translate('Type', category: 'dboard-modules'); ?></dt>
                    <dd class="mt-1 text-gray-900"><?php echo Html::encode($typeLabel); ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500"><?php echo $translator->translate('Name', category: 'dboard-common'); ?></dt>
                    <dd class="mt-1 text-gray-900"><?php echo Html::encode($name); ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Slug</dt>
                    <dd class="mt-1 text-gray-900"><?php echo Html::encode($slugPath); ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500"><?php echo $translator->translate('Tree path', category: 'dboard-modules'); ?></dt>
                    <dd class="mt-1 text-gray-900"><?php echo Html::encode($treePath); ?></dd>
                </div>
            </dl>
        </div>

        <?php if ($exists): ?>
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
                <?php echo Bleet::input()->hidden()->active($formModel, 'mode')->value('overwrite')->render(); ?>
                <div class="flex justify-between items-center">
                    <?php echo Bleet::a($translator->translate('Previous', category: 'dboard-common'))
                        ->url($urlGenerator->generate('dboard.import.step1'))
                        ->icon('arrow-left')
                        ->ghost()
                        ->secondary()
                        ->render();
                    ?>
                    <?php echo Bleet::button($translator->translate('Overwrite existing {type}', ['type' => lcfirst($typeLabel)], 'dboard-modules'))
                        ->icon('exclamation-triangle')
                        ->submit()
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
                <?php echo Bleet::input()->hidden()->active($formModel, 'mode')->value('create')->render(); ?>
                <div class="mb-4">
                    <?php echo Bleet::label()
                        ->active($formModel, 'targetPath')
                        ->primary()
                        ->render();
                    ?>
                    <div class="mt-2">
                        <?php echo Bleet::select()
                            ->active($formModel, 'targetPath')
                            ->options($treeOptions)
                            ->primary()
                            ->render();
                        ?>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <?php echo Bleet::a($translator->translate('Previous', category: 'dboard-common'))
                        ->url($urlGenerator->generate('dboard.import.step1'))
                        ->icon('arrow-left')
                        ->ghost()
                        ->secondary()
                        ->render();
                    ?>
                    <?php echo Bleet::button($translator->translate('Create new {type}', ['type' => lcfirst($typeLabel)], 'dboard-modules'))
                        ->icon('plus')
                        ->submit()
                        ->primary()
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
                <?php echo Bleet::input()->hidden()->active($formModel, 'mode')->value('create')->render(); ?>
                <div>
                    <?php echo Bleet::label()
                        ->active($formModel, 'targetPath')
                        ->primary()
                        ->render();
                    ?>
                    <div class="mt-2">
                        <?php echo Bleet::select()
                            ->active($formModel, 'targetPath')
                            ->options($treeOptions)
                            ->primary()
                            ->render();
                        ?>
                    </div>
                </div>
                <div class="flex justify-end gap-4 mt-6">
                    <?php echo Bleet::button($translator->translate('Continue', category: 'dboard-common'))
                        ->icon('arrow-right')
                        ->submit()
                        ->primary()
                        ->render();
                    ?>
                </div>
            <?php echo Html::form()->close(); ?>
        <?php endif; ?>
    </div>
</main>
