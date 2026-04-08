<?php

declare(strict_types=1);

/**
 * _xeo-llm-content.php
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\LlmMenu;
use Blackcube\Dboard\Models\Forms\XeoLlmForm;
use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var TranslatorInterface $translator
 * @var LlmMenu|null $llmMenu
 * @var LlmMenu[] $categories
 * @var XeoLlmForm $linkFormModel
 * @var string $llmRefreshUrl
 */

$llmMenu = $llmMenu ?? null;
$categories = $categories ?? [];
?>

<?php if ($llmMenu !== null): ?>
    <?php
    $parent = $llmMenu->relativeQuery()->parent()->one();
    $parentName = $parent !== null ? $parent->getName() : '';
    ?>
    <div class="flex items-center justify-between p-3 bg-secondary-50 rounded-lg">
        <div>
            <span class="font-medium text-sm text-secondary-700"><?php echo Html::encode($parentName); ?></span>
            <span class="text-secondary-400"> / </span>
            <span class="text-sm text-secondary-600"><?php echo Html::encode($llmMenu->getName()); ?></span>
        </div>
        <?php echo Bleet::button()
            ->icon('x-mark')
            ->outline()
            ->xs()
            ->danger()
            ->addAttributes(Bleet::ajaxify()->url($llmRefreshUrl)->verb('DELETE')->id('xeo-llm')->trigger())
            ->render(); ?>
    </div>
<?php elseif (!empty($categories)): ?>
    <?php
    $categoryOptions = [];
    foreach ($categories as $category) {
        $categoryOptions[$category->getId()] = $category->getName();
    }
    ?>
    <div class="flex gap-2">
        <?php echo Bleet::select()
            ->active($linkFormModel, 'parentId')
            ->options($categoryOptions)
            ->placeholder($translator->translate('-- Select --', category: 'dboard-common'))
            ->fieldData(['ajaxify' => 'xeo-llm-add'])
            ->secondary()
            ->wrapperAddClass('flex-1')
            ->render(); ?>
        <?php echo Bleet::button()
            ->icon('plus')
            ->outline()
            ->xs()
            ->primary()
            ->addAttributes(Bleet::ajaxify()->url($llmRefreshUrl)->verb('POST')->id('xeo-llm-add')->trigger())
            ->render(); ?>
    </div>
<?php else: ?>
    <p class="text-sm text-secondary-500 italic"><?php echo $translator->translate('No LLM category available.', category: 'dboard-common'); ?></p>
<?php endif; ?>
