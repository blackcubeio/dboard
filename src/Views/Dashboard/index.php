<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\ContentQuery;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dcore\Models\TagQuery;
use Blackcube\Dboard\Helpers\RelativeTimeHelper;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Widgets\Widgets;
use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var UrlGeneratorInterface $urlGenerator
 * @var TranslatorInterface $translator
 * @var Administrator $administrator
 * @var array{contentsActive: int, contentsOutOfDate: int, contentsInactive: int, tagsActive: int, tagsInactive: int} $stats
 * @var ContentQuery $latestContentsQuery
 * @var TagQuery $latestTagsQuery
 * @var string|null $csrf
 */

$fullName = trim($administrator->getFirstname() . ' ' . $administrator->getLastname());

?>

        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <?php echo Bleet::h1($translator->translate('Dashboard', category: 'dboard-modules'))->primary(); ?>

            <div class="flex flex-wrap gap-4 mb-8">
                <?php echo Bleet::statCard($translator->translate('Active contents', category: 'dboard-modules'), (string) $stats['contentsActive'])
                    ->icon('check-circle')
                    ->success()
                    ->addClass('flex-1');
                ?>
                <?php echo Bleet::statCard($translator->translate('Out of date contents', category: 'dboard-modules'), (string) $stats['contentsOutOfDate'])
                    ->icon('clock')
                    ->warning()
                    ->addClass('flex-1');
                ?>
                <?php echo Bleet::statCard($translator->translate('Inactive contents', category: 'dboard-modules'), (string) $stats['contentsInactive'])
                    ->icon('x-circle')
                    ->danger()
                    ->addClass('flex-1');
                ?>
            </div>

            <?php echo Bleet::h2($translator->translate('Overview', category: 'dboard-modules'))->primary(); ?>

            <div class="flex flex-wrap gap-4 items-stretch">
                <div class="w-full lg:flex-1 flex flex-col">
                    <?php
                    echo Bleet::cardHeader()
                        ->icon('document-text')
                        ->title($translator->translate('Contents', category: 'dboard-modules'))
                        ->badges([
                            Bleet::badge((string) $stats['contentsActive'])->dot()->success(),
                            Bleet::badge((string) $stats['contentsInactive'])->dot()->danger(),
                            Bleet::badge((string) $stats['contentsOutOfDate'])->dot()->warning(),
                        ])
                        ->primary();

                    $contentsFeed = Bleet::activityFeed()
                        ->title($translator->translate('Latest updated contents', category: 'dboard-modules'));

                    /** @var Content $content */
                    foreach ($latestContentsQuery->each() as $content):
                        $timestamp = RelativeTimeHelper::format($content->getDateUpdate(), $translator);
                        $contentName = Html::a(
                            Html::encode($content->getName() ?? $translator->translate('Unnamed', category: 'dboard-modules')),
                            $urlGenerator->generate('dboard.contents.edit', [
                                'id' => $content->getId(),
                            ])
                        )->render();
                        $contentsFeed = $contentsFeed->addItem(
                            Bleet::activityItem($contentName)
                                ->encode(false)
                                ->icon('document-text')
                                ->primary()
                                ->timestamp($timestamp)
                        );
                    endforeach;
                    ?>
                    <div class="bg-white rounded-b-lg shadow-lg p-4 flex-1">
                        <?php echo $contentsFeed->unstyled()->primary(); ?>
                    </div>
                </div>

                <div class="w-full lg:flex-1 flex flex-col">
                    <?php
                    echo Bleet::cardHeader()
                        ->icon('tag')
                        ->title($translator->translate('Tags', category: 'dboard-modules'))
                        ->badges([
                            Bleet::badge((string) $stats['tagsActive'])->dot()->success(),
                            Bleet::badge((string) $stats['tagsInactive'])->dot()->danger(),
                            Bleet::badge('0')->dot()->warning(),
                        ])
                        ->primary();

                    $tagsFeed = Bleet::activityFeed()
                        ->title($translator->translate('Latest updated tags', category: 'dboard-modules'));

                    /** @var Tag $tag */
                    foreach ($latestTagsQuery->each() as $tag):
                        $timestamp = RelativeTimeHelper::format($tag->getDateUpdate(), $translator);
                        $tagName = Html::a(
                            Html::encode($tag->getName()),
                            $urlGenerator->generate('dboard.tags.edit', [
                                'id' => $tag->getId(),
                            ])
                        )->render();
                        $tagsFeed = $tagsFeed->addItem(
                            Bleet::activityItem($tagName)
                                ->encode(false)
                                ->icon('tag')
                                ->primary()
                                ->timestamp($timestamp)
                        );
                    endforeach;
                    ?>
                    <div class="bg-white rounded-b-lg shadow-lg p-4 flex-1">
                        <?php echo $tagsFeed->unstyled()->primary(); ?>
                    </div>
                </div>
            </div>
        </main>
