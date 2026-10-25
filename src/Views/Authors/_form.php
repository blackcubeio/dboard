<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Author;
use Blackcube\Dboard\Models\Forms\AuthorForm;
use Blackcube\Bleet\Bleet;
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
 * @var array $fileEndpoints
 */

?>

                <div class="bg-white rounded-b-lg shadow-lg p-6">
                    <div class="basis-full mb-4">
                        <?php echo Bleet::toggle($formModel, 'active')
                            ->secondary()
                            ->render();
                        ?>
                    </div>

                    <div class="flex gap-4">
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::input($formModel, 'firstname')
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::input($formModel, 'lastname')
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-4">
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::input($formModel, 'jobTitle')
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::input($formModel, 'email')
                                ->email()
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('Profile', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::input($formModel, 'url')
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::upload($formModel, 'image')
                                ->endpoint($fileEndpoints['upload'])
                                ->previewEndpoint($fileEndpoints['preview'])
                                ->deleteEndpoint($fileEndpoints['delete'])
                                ->accept([
                                    'png',
                                    'jpg',
                                    'jpeg',
                                    'webp',
                                ])
                                ->dropzoneText($translator->translate('Drop file here', category: 'dboard-common'))
                                ->browseSeparator($translator->translate('or', category: 'dboard-common'))
                                ->browseLabel($translator->translate('browse', category: 'dboard-common'))
                                ->typeErrorText($translator->translate('The file "{name}" is not an accepted type ({types}).', [
                                    'name' => '{name}',
                                    'types' => '{types}',
                                ], 'dboard-common'))
                                ->responseErrorText($translator->translate('Invalid server response.', category: 'dboard-common'))
                                ->uploadErrorText($translator->translate('Failed to upload "{name}".', [
                                    'name' => '{name}',
                                ], 'dboard-common'))
                                ->errorTitle($translator->translate('Error', category: 'dboard-common'))
                                ->secondary()
                                ->render(); ?>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-4">
                        <div class="basis-full">
                            <?php echo Bleet::textarea($formModel, 'sameAs')
                                ->rows(3)
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('Expertise', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::textarea($formModel, 'worksFor')
                                ->rows(2)
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::textarea($formModel, 'knowsAbout')
                                ->rows(2)
                                ->secondary()
                                ->render();
                            ?>
                        </div>
                    </div>
                </div>
