<?php

declare(strict_types=1);

/**
 * _form.php
 * @copyright 2010-2026 Blackcube
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
                        <?php echo Bleet::toggle()
                            ->active($formModel, 'active')
                            ->primary()
                            ->render();
                        ?>
                    </div>

                    <div class="flex gap-4">
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'firstname')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::input()
                                    ->active($formModel, 'firstname')
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'lastname')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::input()
                                    ->active($formModel, 'lastname')
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-4">
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'jobTitle')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::input()
                                    ->active($formModel, 'jobTitle')
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'email')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::input()
                                    ->active($formModel, 'email')
                                    ->email()
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('Profile', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'url')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::input()
                                    ->active($formModel, 'url')
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::upload()
                                ->active($formModel, 'image')
                                ->endpoint($fileEndpoints['upload'])
                                ->previewEndpoint($fileEndpoints['preview'])
                                ->deleteEndpoint($fileEndpoints['delete'])
                                ->accept(['png', 'jpg', 'jpeg', 'webp'])
                                ->primary()
                                ->render(); ?>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-4">
                        <div class="basis-full">
                            <?php echo Bleet::label()
                                ->active($formModel, 'sameAs')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::textarea()
                                    ->active($formModel, 'sameAs')
                                    ->rows(3)
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php echo Bleet::hr($translator->translate('Expertise', category: 'dboard-modules'))->secondary(); ?>

                    <div class="flex gap-4">
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'worksFor')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::textarea()
                                    ->active($formModel, 'worksFor')
                                    ->rows(2)
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                        <div class="basis-1/2 grow">
                            <?php echo Bleet::label()
                                ->active($formModel, 'knowsAbout')
                                ->primary()
                                ->render();
                            ?>
                            <div class="mt-2">
                                <?php echo Bleet::textarea()
                                    ->active($formModel, 'knowsAbout')
                                    ->rows(2)
                                    ->primary()
                                    ->render();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
