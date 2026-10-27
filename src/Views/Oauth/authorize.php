<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Components\Rbac;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\ConsentForm;
use Blackcube\Dboard\Models\OauthClient;
use Blackcube\Dboard\Services\AddonPopulation;
use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var TranslatorInterface $translator
 * @var AddonPopulation $population
 * @var OauthClient $client
 * @var Administrator $administrator
 * @var array<string, array<string, string>> $groups
 * @var ConsentForm $form
 * @var string $formAction
 * @var string|null $csrf
 */

?>
<div class="flex min-h-screen items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
    <div class="w-full max-w-xl">
        <div class="flex flex-col items-center">
            <div class="rounded-xl bg-primary-950 p-2 text-white dark:bg-white/10">
                <?php echo Bleet::svg()
                        ->logo('blackcube')
                        ->addClass('size-16');
                ?>
            </div>
        </div>
        <div class="dboard-surface mt-8 px-6 py-10 sm:px-12">
            <?php echo Html::form()
                    ->post($formAction)
                    ->csrf($csrf)
                    ->noValidate()
                    ->addClass('space-y-6')
                    ->open();
            ?>
                <div>
                    <?php echo Bleet::h3($translator->translate('Authorize access', category: 'dboard-modules'))->render(); ?>
                    <?php echo Bleet::p($translator->translate('{client} asks to act in dboard as {administrator}.', [
                        'client' => $client->getName(),
                        'administrator' => $administrator->getName(),
                    ], 'dboard-modules'))->secondary()->render(); ?>
                </div>

                <?php if (empty($groups) === true): ?>
                    <?php echo Bleet::alert($translator->translate('Your account holds none of the rights this application asks for.', category: 'dboard-modules'))->warning()->render(); ?>
                <?php else: ?>
                    <?php echo Bleet::p($translator->translate('Choose the rights to grant. Each one is bounded by your own permissions.', category: 'dboard-modules'))->secondary()->render(); ?>
                    <div class="space-y-4">
                        <?php foreach ($groups as $role => $scopeOptions): ?>
                            <div class="overflow-hidden rounded-lg border border-secondary-200 dark:border-white/10">
                                <div class="flex items-center gap-2 bg-secondary-50 px-4 py-3">
                                    <?php echo Bleet::svg()->outline('shield-check')->addClass('size-5', 'text-secondary-600')->render(); ?>
                                    <span class="font-medium text-secondary-900"><?php echo Html::encode($role); ?></span>
                                </div>
                                <?php echo Bleet::toggleList($form, 'scopes')
                                    ->items($scopeOptions)
                                    ->hideLabel()
                                    ->secondary()
                                    ->render();
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="flex gap-3 border-t border-secondary-200 pt-6 dark:border-white/10">
                    <?php echo Bleet::submitButton($translator->translate('Deny', category: 'dboard-modules'), $form, 'decision')
                            ->value(ConsentForm::DECISION_DENY)
                            ->ghost()
                            ->secondary()
                            ->render();
                    ?>
                    <?php if (empty($groups) === false): ?>
                        <?php echo Bleet::submitButton($translator->translate('Allow', category: 'dboard-modules'), $form, 'decision')
                                ->value(ConsentForm::DECISION_ALLOW)
                                ->accent()
                                ->render();
                        ?>
                    <?php endif; ?>
                </div>
            <?php echo Html::form()
                    ->close();
            ?>
        </div>
    </div>
</div>
