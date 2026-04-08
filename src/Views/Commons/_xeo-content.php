<?php

declare(strict_types=1);

/**
 * _xeo-content.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Author;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dcore\Models\LlmMenu;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dboard\Models\Forms\XeoLlmForm;
use Blackcube\Dboard\Helpers\ElasticFieldRenderer;
use Blackcube\Dboard\Models\Forms\XeoAuthorForm;
use Blackcube\Dboard\Models\Forms\XeoBlocForm;
use Blackcube\Dboard\Models\Forms\XeoForm;
use Blackcube\Bleet\Aurelia;
use Blackcube\Bleet\Bleet;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var TranslatorInterface $translator
 * @var ActiveRecord $model
 * @var Slug $slug
 * @var XeoForm $formModel
 * @var UrlGeneratorInterface $urlGenerator
 * @var array $fileEndpoints
 * @var string $formAction
 * @var string|null $csrf
 * @var array<int, XeoBlocForm> $xeoBlocForms
 * @var array<int, ElasticSchema> $xeoBlocSchemas
 * @var string|null $refreshUrl
 * @var string $suggestUrl
 * @var array $canonicalOptions
 * @var XeoAuthorForm[] $xeoAuthorForms
 * @var Author[] $availableAuthors
 * @var string $authorsRefreshUrl
 * @var LlmMenu|null $llmMenu
 * @var LlmMenu[] $llmCategories
 * @var XeoLlmForm $llmLinkFormModel
 * @var string $llmRefreshUrl
 */

$ogTypeOptions = XeoForm::getOgTypeOptions();
$twitterCardOptions = XeoForm::getTwitterCardOptions();
$xeoBlocForms = $xeoBlocForms ?? [];
$xeoBlocSchemas = $xeoBlocSchemas ?? [];
$refreshUrl = $refreshUrl ?? null;
$xeoAuthorForms = $xeoAuthorForms ?? [];
$availableAuthors = $availableAuthors ?? [];
$authorsRefreshUrl = $authorsRefreshUrl ?? '';
$llmMenu = $llmMenu ?? null;
$llmCategories = $llmCategories ?? [];
$llmLinkFormModel = $llmLinkFormModel ?? new XeoLlmForm();
$llmRefreshUrl = $llmRefreshUrl ?? '';
?>
<div class="p-6">
    <?php echo Html::form()
        ->method('POST')
        ->action($formAction)
        ->csrf($csrf)
        ->noValidate()
        ->open(); ?>

    <div class="mb-4">
        <p class="text-sm text-secondary-600">
            <?php echo $translator->translate('Slug:', category: 'dboard-common'); ?> <strong><?php echo Html::encode($slug->getPath()); ?></strong>
        </p>
    </div>

    <div class="space-y-6">
        <!-- Basic XEO Section -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-semibold text-secondary-900"><?php echo $translator->translate('Basic XEO', category: 'dboard-content'); ?></h4>
                <?php echo Bleet::button($translator->translate('Suggest', category: 'dboard-common'))
                    ->icon('sparkles')
                    ->outline()
                    ->xs()
                    ->secondary()
                    ->addAttributes(Bleet::ajaxify()->url($suggestUrl)->id('xeo-suggest')->trigger())
                    ->render(); ?>
            </div>
            <div class="space-y-4">
                <div>
                    <?php echo Bleet::toggle()
                        ->active($formModel, 'active')
                        ->secondary()
                        ->render(); ?>
                </div>
                <bleet-ajaxify id="xeo-suggest">
                <?php echo $this->render('Commons/_xeo-suggest-content', [
                    'formModel' => $formModel,
                    'fileEndpoints' => $fileEndpoints,
                ]); ?>
                </bleet-ajaxify>

                <div>
                    <?php echo Bleet::label()->active($formModel, 'canonicalSlugId')->secondary()->render(); ?>
                    <div class="mt-2">
                        <?php echo Bleet::select()
                            ->active($formModel, 'canonicalSlugId')
                            ->options($canonicalOptions)
                            ->searchable()
                            ->secondary()
                            ->render(); ?>
                    </div>
                </div>

            </div>
        </div>
        <!-- LLMs Section -->
        <div class="border-t border-gray-200 pt-6">
            <h4 class="text-sm font-semibold text-secondary-900 mb-4"><?php echo $translator->translate('LLMs', category: 'dboard-common'); ?></h4>
            <bleet-ajaxify id="xeo-llm" url="<?php echo $llmRefreshUrl; ?>">
            <?php echo $this->render('Commons/_xeo-llm-content', [
                    'llmMenu' => $llmMenu,
                    'categories' => $llmCategories,
                    'linkFormModel' => $llmLinkFormModel,
                    'llmRefreshUrl' => $llmRefreshUrl,
            ]); ?>
            </bleet-ajaxify>
        </div>
        <!-- Authors Section (E-E-A-T) -->
        <div class="border-t border-gray-200 pt-6">
            <h4 class="text-sm font-semibold text-secondary-900 mb-4"><?php echo $translator->translate('Authors (E-E-A-T)', category: 'dboard-content'); ?></h4>
            <?php echo Html::openTag('dboard-authors', Aurelia::attributesCustomElement([
                    'buildUrl' => $authorsRefreshUrl,
            ])); ?>
            <?php echo $this->render('Commons/_xeo-authors-content', [
                    'xeoAuthorForms' => $xeoAuthorForms,
                    'availableAuthors' => $availableAuthors,
                    'authorsRefreshUrl' => $authorsRefreshUrl,
            ]); ?>
            <?php echo Html::closeTag('dboard-authors'); ?>
        </div>
        <!-- Open Graph Section -->
        <div class="border-t border-gray-200 pt-6">
            <h4 class="text-sm font-semibold text-secondary-900 mb-4">Open Graph</h4>
            <div class="space-y-4">
                <div>
                    <?php echo Bleet::checkbox()
                        ->active($formModel, 'og')
                        ->secondary()
                        ->render(); ?>
                </div>

                <div>
                    <?php echo Bleet::label()->active($formModel, 'ogType')->secondary()->render(); ?>
                    <div class="mt-2">
                        <?php echo Bleet::select()
                            ->active($formModel, 'ogType')
                            ->options($ogTypeOptions)
                            ->secondary()
                            ->render(); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Twitter Section -->
        <div class="border-t border-gray-200 pt-6">
            <h4 class="text-sm font-semibold text-secondary-900 mb-4">Twitter Cards</h4>
            <div class="space-y-4">
                <div>
                    <?php echo Bleet::checkbox()
                        ->active($formModel, 'twitter')
                        ->secondary()
                        ->render(); ?>
                </div>

                <div>
                    <?php echo Bleet::label()->active($formModel, 'twitterCard')->secondary()->render(); ?>
                    <div class="mt-2">
                        <?php echo Bleet::select()
                            ->active($formModel, 'twitterCard')
                            ->options($twitterCardOptions)
                            ->secondary()
                            ->render(); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Structured Data Section -->
        <?php if (!empty($xeoBlocForms) || $refreshUrl !== null): ?>
            <div class="border-t border-gray-200 pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-semibold text-secondary-900"><?php echo $translator->translate('Structured data', category: 'dboard-content'); ?></h4>
                    <?php if ($refreshUrl !== null): ?>
                        <?php echo Bleet::button($translator->translate('Refresh', category: 'dboard-common'))
                            ->submit()
                            ->active($formModel, 'refresh')
                            ->value(true)
                            ->icon('arrow-path')
                            ->outline()
                            ->xs()
                            ->secondary()
                            ->render(); ?>
                    <?php endif; ?>
                </div>
                <?php if ($refreshUrl !== null): ?>
                    <p class="text-xs text-warning-600 mb-4">
                        <?php echo Bleet::svg()->solid('exclamation-triangle')->addClass('size-4 inline-block mr-1')->render(); ?>
                        <?php echo $translator->translate('Warning: refreshing will replace all existing structured data blocks.', category: 'dboard-content'); ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($xeoBlocForms)): ?>
                    <div class="space-y-4">
                        <?php foreach ($xeoBlocForms as $blocId => $xeoBlocForm): ?>
                            <?php
                            $schema = $xeoBlocSchemas[$blocId] ?? null;
                            $schemaName = $schema !== null ? $schema->getName() : $translator->translate('Block', category: 'dboard-common');
                            $isActive = $xeoBlocForm->isXeoBlocActive();
                            ?>
                            <?php echo Html::openTag('div', ['class' => $isActive ? 'border border-secondary-200 rounded-lg overflow-hidden' : 'border border-warning-200 rounded-lg overflow-hidden']); ?>
                                <?php echo Html::openTag('div', ['class' => $isActive ? 'bg-secondary-100 px-4 py-2 flex items-center justify-between' : 'bg-warning-100 px-4 py-2 flex items-center justify-between']); ?>
                                    <h4 class="font-medium text-secondary-700">
                                        <?php echo Html::encode($schemaName); ?>
                                    </h4>
                                    <div>
                                        <?php echo Bleet::toggle()
                                            ->active($xeoBlocForm, '[' . $blocId . ']xeoBlocActive')
                                            ->secondary()
                                            ->render(); ?>
                                    </div>
                                <?php echo Html::closeTag('div'); ?>
                                <div class="p-4">
                                    <?php
                                    $adminTemplate = ElasticFieldRenderer::getAdminView($schema);
                                    if ($adminTemplate !== false) {
                                        $formClass = get_class($xeoBlocForm);
                                        $elasticProperties = array_filter($xeoBlocForm->getProperties(), function ($property) use ($formClass) {
                                            return $property->isElastic($formClass);
                                        });
                                        $elasticAttributes = array_keys($elasticProperties);
                                        echo $this->render($adminTemplate, [
                                            'blocId' => $blocId,
                                            'blocForm' => $xeoBlocForm,
                                            'attributes' => $elasticAttributes,
                                            'fileEndpoints' => $fileEndpoints,
                                        ]);
                                    } else {
                                        echo ElasticFieldRenderer::renderAll($xeoBlocForm, '[' . $blocId . ']', $fileEndpoints);
                                    }
                                    ?>
                                </div>
                            <?php echo Html::closeTag('div'); ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-secondary-500 italic"><?php echo $translator->translate('No structured data blocks.', category: 'dboard-content'); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

    <div class="flex gap-4 pt-6 mt-6 border-t border-gray-200">
        <?php echo Bleet::button($translator->translate('Close', category: 'dboard-common'))
            ->secondary()
            ->attribute('data-drawer', 'close')
            ->render(); ?>

        <?php echo Bleet::button($translator->translate('Save', category: 'dboard-common'))
            ->submit()
            ->primary()
            ->render(); ?>
    </div>

    <?php echo Html::closeTag('form'); ?>
</div>
