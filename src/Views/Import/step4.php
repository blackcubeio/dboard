<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var array $importedElement
 * @var string $elementType
 * @var string $mode
 * @var string|null $targetPath
 * @var array $corrections
 * @var string|null $error
 * @var string|null $csrf
 */

$typeLabel = $elementType === 'content' ? $translator->translate('Content', category: 'dboard-modules') : $translator->translate('Tag', category: 'dboard-modules');
$modeLabel = $mode === 'overwrite' ? $translator->translate('Overwrite existing', category: 'dboard-modules') : $translator->translate('Create new', category: 'dboard-modules');
$name = $importedElement['name'] ?? $translator->translate('(no name)', category: 'dboard-modules');
$slugPath = $corrections['slugPath'] ?? ($importedElement['slug'] !== null ? ($importedElement['slug']['path'] ?? $translator->translate('(no slug)', category: 'dboard-modules')) : $translator->translate('(no slug)', category: 'dboard-modules'));
$blocsCount = count($importedElement['blocs'] ?? []);
$xeoBlocsCount = ($importedElement['slug'] !== null) ? count($importedElement['slug']['xeo']['blocs'] ?? []) : 0;
$tagsCount = count($importedElement['tags'] ?? []);
$authorsCount = count($importedElement['authors'] ?? []);

?>
<main class="flex-1 p-4 sm:p-6 lg:p-8">
    <div class="mb-8">
        <?php echo Bleet::step()
            ->current(3)
            ->addStep('Upload', $urlGenerator->generate('dboard.import.step1'))
            ->addStep('Existence', $urlGenerator->generate('dboard.import.step2'))
            ->addStep($translator->translate('References', category: 'dboard-modules'), $urlGenerator->generate('dboard.import.step3'))
            ->addStep($translator->translate('Import', category: 'dboard-modules'), $urlGenerator->generate('dboard.import.step4'))
            ->render();
        ?>
    </div>

    <?php echo Bleet::cardHeader()
        ->icon('arrow-up-tray')
        ->title($translator->translate('Summary', category: 'dboard-modules'))
        ->primary();
    ?>

    <div class="bg-white rounded-b-lg shadow-lg p-8">
        <?php if ($error !== null): ?>
            <div class="mb-6">
                <?php echo Bleet::alert()->content($error)->danger()->render(); ?>
            </div>
        <?php endif; ?>

        <div class="mb-6 rounded-md border border-gray-200 p-4">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="font-medium text-gray-500"><?php echo $translator->translate('Element type', category: 'dboard-common'); ?></dt>
                    <dd class="mt-1 text-gray-900"><?php echo Html::encode($typeLabel); ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500"><?php echo $translator->translate('Mode', category: 'dboard-common'); ?></dt>
                    <dd class="mt-1 text-gray-900"><?php echo Html::encode($modeLabel); ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500"><?php echo $translator->translate('Name', category: 'dboard-common'); ?></dt>
                    <dd class="mt-1 text-gray-900"><?php echo Html::encode($name); ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Slug</dt>
                    <dd class="mt-1 text-gray-900"><?php echo Html::encode($slugPath); ?></dd>
                </div>
                <?php if ($targetPath !== null): ?>
                    <div>
                        <dt class="font-medium text-gray-500"><?php echo $translator->translate('Position', category: 'dboard-common'); ?></dt>
                        <dd class="mt-1 text-gray-900"><?php echo $translator->translate('Under {path}', [
                            'path' => Html::encode($targetPath),
                        ], 'dboard-modules'); ?></dd>
                    </div>
                <?php endif; ?>
                <div>
                    <dt class="font-medium text-gray-500"><?php echo $translator->translate('{type, select, content{Content blocks} other{Tag blocks}}', [
                        'type' => $elementType,
                    ], 'dboard-common'); ?></dt>
                    <dd class="mt-1 text-gray-900"><?php echo $blocsCount; ?></dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500"><?php echo $translator->translate('XEO Blocks', category: 'dboard-common'); ?></dt>
                    <dd class="mt-1 text-gray-900"><?php echo $xeoBlocsCount; ?></dd>
                </div>
                <?php if ($elementType === 'content'): ?>
                    <div>
                        <dt class="font-medium text-gray-500">Tags</dt>
                        <dd class="mt-1 text-gray-900"><?php echo $tagsCount; ?></dd>
                    </div>
                <?php endif; ?>
                <div>
                    <dt class="font-medium text-gray-500"><?php echo $translator->translate('Authors', category: 'dboard-common'); ?></dt>
                    <dd class="mt-1 text-gray-900"><?php echo $authorsCount; ?></dd>
                </div>
            </dl>
        </div>

        <?php if (empty($corrections) === false): ?>
            <div class="mb-6">
                <?php
                $correctionLines = [];
                if (isset($corrections['slugPath']) === true):
                    $correctionLines[] = $translator->translate('Slug replaced by "{slug}"', [
                        'slug' => Html::encode($corrections['slugPath']),
                    ], 'dboard-modules');
                endif;
                if (isset($corrections['languageId']) === true):
                    $correctionLines[] = $translator->translate('Language replaced by ID {id}', [
                        'id' => Html::encode($corrections['languageId']),
                    ], 'dboard-modules');
                endif;
                if (isset($corrections['typeId']) === true):
                    $correctionLines[] = $translator->translate('Type replaced by ID {id}', [
                        'id' => Html::encode((string) $corrections['typeId']),
                    ], 'dboard-modules');
                endif;
                if (isset($corrections['hostId']) === true):
                    $correctionLines[] = $translator->translate('Host replaced by ID {id}', [
                        'id' => Html::encode((string) $corrections['hostId']),
                    ], 'dboard-modules');
                endif;
                if (isset($corrections['authors']) === true):
                    $correctionLines[] = $translator->translate('{count, plural, one{# author replaced} other{# authors replaced}}', [
                        'count' => count($corrections['authors']),
                    ], 'dboard-modules');
                endif;
                if (isset($corrections['blocs']) === true):
                    $correctionLines[] = $translator->translate('{count, plural, one{# block schema replaced} other{# block schemas replaced}}', [
                        'count' => count($corrections['blocs']),
                    ], 'dboard-modules');
                endif;
                if (isset($corrections['xeoBlocs']) === true):
                    $correctionLines[] = $translator->translate('{count, plural, one{# XEO block schema replaced} other{# XEO block schemas replaced}}', [
                        'count' => count($corrections['xeoBlocs']),
                    ], 'dboard-modules');
                endif;
                echo Bleet::alert()->content($translator->translate('Applied corrections: {details}', [
                    'details' => implode(' — ', $correctionLines),
                ], 'dboard-modules'))
                    ->warning()
                    ->render();
                ?>
            </div>
        <?php endif; ?>

        <?php echo Html::form()
            ->post($urlGenerator->generate('dboard.import.step4'))
            ->csrf($csrf)
            ->open();
        ?>
        <div class="flex justify-between items-center mt-6">
            <?php echo Bleet::a($translator->translate('Previous', category: 'dboard-common'))
                ->url($urlGenerator->generate('dboard.import.step3'))
                ->icon('arrow-left')
                ->ghost()
                ->secondary()
                ->render();
            ?>
            <?php echo Bleet::submitButton($translator->translate('Import', category: 'dboard-modules'))
                ->icon('arrow-down-tray')
                ->primary()
                ->render();
            ?>
        </div>
        <?php echo Html::form()->close(); ?>
    </div>
</main>
