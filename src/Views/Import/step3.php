<?php

declare(strict_types=1);

/**
 * step3.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Blackcube\Dboard\Models\Forms\ImportReferencesForm;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var array $data
 * @var string $elementType
 * @var array $missing
 * @var array $warnings
 * @var array $options
 * @var ImportReferencesForm $formModel
 * @var bool $slugConflict
 * @var string|null $csrf
 */

$typeLabel = $elementType === 'content' ? $translator->translate('Content', category: 'dboard-modules') : $translator->translate('Tag', category: 'dboard-modules');
$hasMissing = !empty($missing);
$hasWarnings = !empty($warnings);
$hasIssues = $hasMissing || $hasWarnings || $slugConflict;

?>
<main class="flex-1 p-4 sm:p-6 lg:p-8">
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

    <?php echo Bleet::cardHeader()
        ->icon('arrow-up-tray')
        ->title($translator->translate('References validation', category: 'dboard-modules'))
        ->primary();
    ?>

    <div class="bg-white rounded-b-lg shadow-lg p-8">
        <?php if (!$hasIssues): ?>
            <div class="mb-6">
                <?php echo Bleet::alert()->content($translator->translate('All references are valid.', category: 'dboard-modules'))->success()->render(); ?>
            </div>
        <?php endif; ?>

        <?php echo Html::form()
            ->post($urlGenerator->generate('dboard.import.step3'))
            ->csrf($csrf)
            ->open();
        ?>

        <?php if ($slugConflict): ?>
            <div class="mb-6">
                <?php echo Bleet::alert()
                    ->content($translator->translate('The slug "{slug}" already exists. Choose a new slug.', ['slug' => Html::encode($data['slug']['path'] ?? '')], 'dboard-modules'))
                    ->warning()
                    ->render();
                ?>
            </div>
            <div class="mb-4">
                <?php echo Bleet::label()
                    ->active($formModel, 'slugPath')
                    ->primary()
                    ->render();
                ?>
                <div class="mt-2">
                    <?php echo Bleet::input()
                        ->active($formModel, 'slugPath')
                        ->primary()
                        ->render();
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($hasMissing): ?>
            <p class="text-sm text-gray-600 mb-4"><?php echo $translator->translate('Some references do not exist in the database. Choose a replacement:', category: 'dboard-modules'); ?></p>

            <?php if (isset($missing['languageId'])): ?>
                <div class="mb-4">
                    <?php echo Bleet::label()
                        ->active($formModel, 'languageId')
                        ->primary()
                        ->render();
                    ?>
                    <p class="text-xs text-gray-500 mb-1"><?php echo $translator->translate('Not found: {value}', ['value' => Html::encode($missing['languageId'])], 'dboard-common'); ?></p>
                    <div class="mt-2">
                        <?php echo Bleet::select()
                            ->active($formModel, 'languageId')
                            ->options($options['languages'])
                            ->placeholder($translator->translate('-- Choose --', category: 'dboard-common'))
                            ->primary()
                            ->render();
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($missing['typeId'])): ?>
                <div class="mb-4">
                    <?php echo Bleet::label()
                        ->active($formModel, 'typeId')
                        ->primary()
                        ->render();
                    ?>
                    <p class="text-xs text-gray-500 mb-1"><?php echo $translator->translate('Not found: {value}', ['value' => 'ID ' . Html::encode((string) $missing['typeId'])], 'dboard-common'); ?></p>
                    <div class="mt-2">
                        <?php echo Bleet::select()
                            ->active($formModel, 'typeId')
                            ->options($options['types'])
                            ->placeholder($translator->translate('-- Choose --', category: 'dboard-common'))
                            ->primary()
                            ->render();
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($missing['hostId'])): ?>
                <div class="mb-4">
                    <?php echo Bleet::label()
                        ->active($formModel, 'hostId')
                        ->primary()
                        ->render();
                    ?>
                    <p class="text-xs text-gray-500 mb-1"><?php echo $translator->translate('Not found: {value}', ['value' => 'ID ' . Html::encode((string) $missing['hostId'])], 'dboard-common'); ?></p>
                    <div class="mt-2">
                        <?php echo Bleet::select()
                            ->active($formModel, 'hostId')
                            ->options($options['hosts'])
                            ->placeholder($translator->translate('-- Choose --', category: 'dboard-common'))
                            ->primary()
                            ->render();
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($missing['authors'])): ?>
                <?php echo Bleet::hr($translator->translate('Authors not found', category: 'dboard-modules'))->secondary(); ?>
                <?php foreach ($missing['authors'] as $i => $authorData): ?>
                    <div class="flex gap-4 items-center mb-4">
                        <span class="text-sm text-gray-500 min-w-[150px]">
                            <?php echo Html::encode(($authorData['firstname'] ?? '') . ' ' . ($authorData['lastname'] ?? '')); ?>
                            (ID <?php echo Html::encode((string) ($authorData['id'] ?? '?')); ?>)
                        </span>
                        <div class="flex-1">
                            <?php echo Bleet::select()
                                ->name('authors[' . $i . ']')
                                ->options($options['authors'])
                                ->placeholder($translator->translate('-- Skip --', category: 'dboard-common'))
                                ->primary()
                                ->render();
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (isset($missing['blocs'])): ?>
                <?php echo Bleet::hr($translator->translate('{type} block schemas not found', ['type' => $typeLabel], 'dboard-modules'))->secondary(); ?>
                <?php foreach ($missing['blocs'] as $i => $blocData): ?>
                    <div class="flex gap-4 items-center mb-4">
                        <span class="text-sm text-gray-500 min-w-[150px]">
                            <?php echo $translator->translate('Block #{number} (schema ID {schemaId})', ['number' => $i + 1, 'schemaId' => Html::encode((string) ($blocData['elasticSchemaId'] ?? '?'))], 'dboard-modules'); ?>
                        </span>
                        <div class="flex-1">
                            <?php echo Bleet::select()
                                ->name('blocs[' . $i . ']')
                                ->options($options['elasticSchemas'])
                                ->placeholder($translator->translate('-- Choose --', category: 'dboard-common'))
                                ->primary()
                                ->render();
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (isset($missing['xeoBlocs'])): ?>
                <?php echo Bleet::hr($translator->translate('XEO block schemas not found', category: 'dboard-modules'))->secondary(); ?>
                <?php foreach ($missing['xeoBlocs'] as $i => $blocData): ?>
                    <div class="flex gap-4 items-center mb-4">
                        <span class="text-sm text-gray-500 min-w-[150px]">
                            <?php echo $translator->translate('XEO Block #{number} (schema ID {schemaId})', ['number' => $i + 1, 'schemaId' => Html::encode((string) ($blocData['elasticSchemaId'] ?? '?'))], 'dboard-modules'); ?>
                        </span>
                        <div class="flex-1">
                            <?php echo Bleet::select()
                                ->name('xeoBlocs[' . $i . ']')
                                ->options($options['elasticSchemas'])
                                ->placeholder($translator->translate('-- Choose --', category: 'dboard-common'))
                                ->primary()
                                ->render();
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($hasWarnings): ?>
            <?php if (isset($warnings['tags'])): ?>
                <div class="mb-4">
                    <?php
                    $tagNames = array_map(fn($t) => $t['name'] ?? ('ID ' . ($t['id'] ?? '?')), $warnings['tags']);
                    echo Bleet::alert()->content($translator->translate('Tags not found (will be skipped): {tags}', ['tags' => implode(', ', $tagNames)], 'dboard-modules'))
                        ->warning()
                        ->render();
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="flex justify-between items-center mt-6">
            <?php echo Bleet::a($translator->translate('Previous', category: 'dboard-common'))
                ->url($urlGenerator->generate('dboard.import.step2'))
                ->icon('arrow-left')
                ->ghost()
                ->secondary()
                ->render();
            ?>
            <?php echo Bleet::button($translator->translate('Continue', category: 'dboard-common'))
                ->icon('arrow-right')
                ->submit()
                ->primary()
                ->render();
            ?>
        </div>

        <?php echo Html::form()->close(); ?>
    </div>
</main>
