<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Blackcube\Dboard\Models\Forms\ImportReferencesForm;
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
 * @var array $missing
 * @var array $warnings
 * @var array $options
 * @var ImportReferencesForm $formModel
 * @var bool $slugConflict
 * @var string|null $csrf
 */

$hasMissing = empty($missing) === false;
$hasWarnings = empty($warnings) === false;
$hasIssues = $hasMissing || $hasWarnings === true || $slugConflict === true;

?>
<main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
    <?php echo Widgets::pageHeading($translator->translate('References validation', category: 'dboard-modules'))->render(); ?>

    <div class="mb-8">
        <?php echo Bleet::step()
            ->current(2)
            ->addStep('Upload', $urlGenerator->generate('dboard.import.step1'))
            ->addStep('Existence', $urlGenerator->generate('dboard.import.step2'))
            ->addStep($translator->translate('References', category: 'dboard-modules'), $urlGenerator->generate('dboard.import.step3'))
            ->addStep($translator->translate('Import', category: 'dboard-modules'))
            ->render();
        ?>
    </div>

    <div class="dboard-surface p-6">
        <?php if ($hasIssues === false): ?>
            <div class="mb-6">
                <?php echo Bleet::alert()->content($translator->translate('All references are valid.', category: 'dboard-modules'))->success()->render(); ?>
            </div>
        <?php endif; ?>

        <?php echo Html::form()
            ->post($urlGenerator->generate('dboard.import.step3'))
            ->csrf($csrf)
            ->open();
        ?>

        <?php if ($slugConflict === true): ?>
            <div class="mb-6">
                <?php echo Bleet::alert()
                    ->content($translator->translate('The slug "{slug}" already exists. Choose a new slug.', [
                        'slug' => Html::encode($importedElement['slug']['path'] ?? ''),
                    ], 'dboard-modules'))
                    ->warning()
                    ->render();
                ?>
            </div>
            <div class="mb-4">
                <?php echo Bleet::input($formModel, 'slugPath')
                    ->secondary()
                    ->render();
                ?>
            </div>
        <?php endif; ?>

        <?php if ($hasMissing === true): ?>
            <p class="mb-4 text-sm text-secondary-500 dark:text-secondary-400"><?php echo $translator->translate('Some references do not exist in the database. Choose a replacement:', category: 'dboard-modules'); ?></p>

            <?php if (isset($missing['languageId']) === true): ?>
                <div class="mb-4">
                    <?php echo Bleet::label('', $formModel, 'languageId')
                        ->secondary()
                        ->render();
                    ?>
                    <p class="mb-1 text-xs text-secondary-500 dark:text-secondary-400"><?php echo $translator->translate('Not found: {value}', [
                        'value' => Html::encode($missing['languageId']),
                    ], 'dboard-common'); ?></p>
                    <div class="mt-2">
                        <?php echo Bleet::select($formModel, 'languageId')
                            ->hideLabel()
                            ->optionsData($options['languages'])
                            ->prompt($translator->translate('-- Choose --', category: 'dboard-common'))
                            ->secondary()
                            ->render();
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($missing['typeId']) === true): ?>
                <div class="mb-4">
                    <?php echo Bleet::label('', $formModel, 'typeId')
                        ->secondary()
                        ->render();
                    ?>
                    <p class="mb-1 text-xs text-secondary-500 dark:text-secondary-400"><?php echo $translator->translate('Not found: {value}', [
                        'value' => 'ID ' . Html::encode((string) $missing['typeId']),
                    ], 'dboard-common'); ?></p>
                    <div class="mt-2">
                        <?php echo Bleet::select($formModel, 'typeId')
                            ->hideLabel()
                            ->optionsData($options['types'])
                            ->prompt($translator->translate('-- Choose --', category: 'dboard-common'))
                            ->secondary()
                            ->render();
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($missing['hostId']) === true): ?>
                <div class="mb-4">
                    <?php echo Bleet::label('', $formModel, 'hostId')
                        ->secondary()
                        ->render();
                    ?>
                    <p class="mb-1 text-xs text-secondary-500 dark:text-secondary-400"><?php echo $translator->translate('Not found: {value}', [
                        'value' => 'ID ' . Html::encode((string) $missing['hostId']),
                    ], 'dboard-common'); ?></p>
                    <div class="mt-2">
                        <?php echo Bleet::select($formModel, 'hostId')
                            ->hideLabel()
                            ->optionsData($options['hosts'])
                            ->prompt($translator->translate('-- Choose --', category: 'dboard-common'))
                            ->secondary()
                            ->render();
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($missing['authors']) === true): ?>
                <?php echo Bleet::hr($translator->translate('Authors not found', category: 'dboard-modules'))->secondary(); ?>
                <?php foreach ($missing['authors'] as $i => $authorData): ?>
                    <div class="flex gap-4 items-center mb-4">
                        <span class="min-w-40 text-sm text-secondary-500 dark:text-secondary-400">
                            <?php echo Html::encode(($authorData['firstname'] ?? '') . ' ' . ($authorData['lastname'] ?? '')); ?>
                            (ID <?php echo Html::encode((string) ($authorData['id'] ?? '?')); ?>)
                        </span>
                        <div class="flex-1">
                            <?php echo Bleet::select()
                                ->name('authors[' . $i . ']')
                                ->optionsData($options['authors'])
                                ->prompt($translator->translate('-- Skip --', category: 'dboard-common'))
                                ->secondary()
                                ->hideLabel()
                                ->render();
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (isset($missing['blocs']) === true): ?>
                <?php echo Bleet::hr($translator->translate('{type, select, content{Content block schemas not found} other{Tag block schemas not found}}', [
                    'type' => $elementType,
                ], 'dboard-modules'))->secondary(); ?>
                <?php foreach ($missing['blocs'] as $i => $blocData): ?>
                    <div class="flex gap-4 items-center mb-4">
                        <span class="min-w-40 text-sm text-secondary-500 dark:text-secondary-400">
                            <?php echo $translator->translate('Block #{number} (schema ID {schemaId})', [
                                'number' => $i + 1,
                                'schemaId' => Html::encode((string) ($blocData['elasticSchemaId'] ?? '?')),
                            ], 'dboard-modules'); ?>
                        </span>
                        <div class="flex-1">
                            <?php echo Bleet::select()
                                ->name('blocs[' . $i . ']')
                                ->optionsData($options['elasticSchemas'])
                                ->prompt($translator->translate('-- Choose --', category: 'dboard-common'))
                                ->secondary()
                                ->hideLabel()
                                ->render();
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (isset($missing['xeoBlocs']) === true): ?>
                <?php echo Bleet::hr($translator->translate('XEO block schemas not found', category: 'dboard-modules'))->secondary(); ?>
                <?php foreach ($missing['xeoBlocs'] as $i => $blocData): ?>
                    <div class="flex gap-4 items-center mb-4">
                        <span class="min-w-40 text-sm text-secondary-500 dark:text-secondary-400">
                            <?php echo $translator->translate('XEO Block #{number} (schema ID {schemaId})', [
                                'number' => $i + 1,
                                'schemaId' => Html::encode((string) ($blocData['elasticSchemaId'] ?? '?')),
                            ], 'dboard-modules'); ?>
                        </span>
                        <div class="flex-1">
                            <?php echo Bleet::select()
                                ->name('xeoBlocs[' . $i . ']')
                                ->optionsData($options['elasticSchemas'])
                                ->prompt($translator->translate('-- Choose --', category: 'dboard-common'))
                                ->secondary()
                                ->hideLabel()
                                ->render();
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($hasWarnings === true): ?>
            <?php if (isset($warnings['tags']) === true): ?>
                <div class="mb-4">
                    <?php
                    $tagNames = [];
                    foreach ($warnings['tags'] as $missingTag):
                        $tagName = 'ID ' . ($missingTag['id'] ?? '?');
                        if (isset($missingTag['name']) === true):
                            $tagName = $missingTag['name'];
                        endif;
                        $tagNames[] = $tagName;
                    endforeach;
                    echo Bleet::alert()->content($translator->translate('Tags not found (will be skipped): {tags}', [
                        'tags' => implode(', ', $tagNames),
                    ], 'dboard-modules'))
                        ->warning()
                        ->render();
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="mt-6 flex items-center justify-between gap-3">
            <?php echo Bleet::a($translator->translate('Previous', category: 'dboard-common'))
                ->url($urlGenerator->generate('dboard.import.step2'))
                ->icon('arrow-left')
                ->ghost()
                ->secondary()
                ->render();
            ?>
            <?php echo Bleet::submitButton($translator->translate('Continue', category: 'dboard-common'))
                ->icon('arrow-right')
                ->accent()
                ->render();
            ?>
        </div>

        <?php echo Html::form()->close(); ?>
    </div>
</main>
