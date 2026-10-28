<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Dashboard;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Handlers\Commons\AbstractBaseHandler;
use Blackcube\Dboard\Services\DashboardService;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Tag;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Dashboard index action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Index extends AbstractBaseHandler
{
    public function __construct(
        LoggerInterface $logger,
        DboardConfig $dboardConfig,
        WebViewRenderer $viewRenderer,
        ResponseFactoryInterface $responseFactory,
        JsonResponseFactory $jsonResponseFactory,
        UrlGeneratorInterface $urlGenerator,
        Aliases $aliases,
        TranslatorInterface $translator,
        protected DashboardService $dashboardService,
    ) {
        parent::__construct(
            logger: $logger,
            dboardConfig: $dboardConfig,
            viewRenderer: $viewRenderer,
            responseFactory: $responseFactory,
            jsonResponseFactory: $jsonResponseFactory,
            urlGenerator: $urlGenerator,
            aliases: $aliases,
            translator: $translator,
        );
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $activeLanguagesCount = $this->dashboardService->getCountActiveLanguages();
        $missingTranslationsCount = 0;
        if ($activeLanguagesCount >= 2) {
            $missingTranslationsCount = $this->dashboardService->getCountMissingTranslations();
        }

        $stats = [
            'contentsActive' => $this->dashboardService->getCountActive(),
            'contentsScheduled' => $this->dashboardService->getCountScheduled(),
            'contentsExpired' => $this->dashboardService->getCountExpired(),
            'contentsInactive' => $this->dashboardService->getCountInactive(),
            'tagsActive' => $this->dashboardService->getCountTagsActive(),
            'tagsInactive' => $this->dashboardService->getCountTagsInactive(),
            'updatedThisWeek' => $this->dashboardService->getCountUpdatedWithin(7),
            'unreachable' => $this->dashboardService->getCountUnreachable(),
            'pagesWithoutXeo' => $this->dashboardService->getCountPagesWithoutXeo(),
            'pagesOutOfSitemap' => $this->dashboardService->getCountPagesOutOfSitemap(),
            'redirections' => $this->dashboardService->getCountRedirections(),
            'inactiveSlugs' => $this->dashboardService->getCountInactiveSlugs(),
            'activeLanguages' => $activeLanguagesCount,
            'missingTranslations' => $missingTranslationsCount,
        ];

        $latestContentsQuery = Content::query()
            ->orderBy([
                'dateUpdate' => SORT_DESC,
                'id' => SORT_DESC,
            ])
            ->limit(5);

        $draftsQuery = Content::query()
            ->active(active: false)
            ->orderBy([
                'dateUpdate' => SORT_DESC,
                'id' => SORT_DESC,
            ])
            ->limit(5);

        $latestTagsQuery = Tag::query()
            ->orderBy([
                'dateUpdate' => SORT_DESC,
                'id' => SORT_DESC,
            ])
            ->limit(5);

        return $this->render('Dashboard/index', [
            'stats' => $stats,
            'latestContentsQuery' => $latestContentsQuery,
            'draftsQuery' => $draftsQuery,
            'latestTagsQuery' => $latestTagsQuery,
            'countsByLanguage' => $this->dashboardService->getCountsByLanguage(),
            'countsByType' => $this->dashboardService->getCountsByType(),
            'urlGenerator' => $this->urlGenerator,
        ]);
    }
}
