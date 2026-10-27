<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Author;
use Blackcube\Dboard\Models\Forms\AuthorForm;
use Blackcube\Dboard\Widgets\Widgets;
use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var Author $author
 * @var AuthorForm $formModel
 * @var CurrentRoute $currentRoute
 * @var string|null $csrf
 * @var array $fileEndpoints
 */

?>
        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            <?php echo Widgets::pageHeading($author->getFirstname() . ' ' . $author->getLastname())
                ->subtitle($translator->translate('Author', category: 'dboard-modules') . ' #' . $author->getId())
                ->backTo($urlGenerator->generate('dboard.authors'), $translator->translate('Authors', category: 'dboard-modules'))
                ->render();
            ?>
            <?php echo Html::form()
                ->post($urlGenerator->generate('dboard.authors.edit', [
                    'id' => $author->getId(),
                ]))
                ->csrf($csrf)
                ->noValidate()
                ->open(); ?>

                <?php echo $this->render('Authors/_form', [
                    'urlGenerator' => $urlGenerator,
                    'author' => $author,
                    'formModel' => $formModel,
                    'currentRoute' => $currentRoute,
                    'fileEndpoints' => $fileEndpoints,
                ]); ?>

                <div class="mt-6 flex justify-end gap-3">
                    <?php echo Bleet::a($translator->translate('Cancel', category: 'dboard-common'))
                        ->url($urlGenerator->generate('dboard.authors'))
                        ->icon('x-mark')
                        ->ghost()
                        ->secondary()
                        ->render();
                    ?>
                    <?php echo Bleet::submitButton($translator->translate('Save', category: 'dboard-common'))
                        ->icon('check')
                        ->accent()
                        ->render();
                    ?>
                </div>
            <?php echo Html::closeTag('form'); ?>
        </main>
