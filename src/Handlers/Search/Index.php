<?php

declare(strict_types=1);

/**
 * Index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Search;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Menu;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Handlers\Commons\AbstractBaseHandler;
use Blackcube\Dboard\Components\Rbac;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\SearchForm;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Method;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Search action - unified search across Contents, Tags, Menus, URLs.
 */
final class Index extends AbstractBaseHandler
{
    protected function getListSize(): int
    {
        return 1;
    }

    public function __construct(
        LoggerInterface $logger,
        DboardConfig $dboardConfig,
        WebViewRenderer $viewRenderer,
        ResponseFactoryInterface $responseFactory,
        JsonResponseFactory $jsonResponseFactory,
        UrlGeneratorInterface $urlGenerator,
        Aliases $aliases,
        TranslatorInterface $translator,
        protected CurrentRoute $currentRoute,
        protected ManagerInterface $rbacManager,
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

        /** @var Administrator $administrator */
        $administrator = $request->getAttribute('administrator');
        $userId = (string) $administrator->getId();

        // Permissions pour chaque section
        $canViewContents = $this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_CONTENT_VIEW);
        $canViewTags = $this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_TAG_VIEW);
        $canViewMenus = $this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_MENU_VIEW);
        $canViewSlugs = $this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_SLUG_VIEW);

        // Create and populate the form from query params
        $searchForm = new SearchForm(translator: $this->translator);
        if ($request->getMethod() === Method::POST) {
            $bodyParams = $request->getParsedBody();
            $searchForm->load($bodyParams);
        } else {
            $searchForm->setFilterContents($canViewContents);
            $searchForm->setFilterTags($canViewTags);
            $searchForm->setFilterMenus($canViewMenus);
            $searchForm->setFilterSlugs($canViewSlugs);
        }

        $search = trim($searchForm->getSearch());

        // Paginators par section (si filtre actif ET permission)
        $paginators = [
            'contents' => null,
            'tags' => null,
            'menus' => null,
            'slugs' => null,
        ];

        if ($search !== '') {
            if ($canViewContents && $searchForm->isFilterContents()) {
                $paginators['contents'] = $this->getContentsPaginator($search, $searchForm->getPageContents());
            }
            if ($canViewTags && $searchForm->isFilterTags()) {
                $paginators['tags'] = $this->getTagsPaginator($search, $searchForm->getPageTags());
            }
            if ($canViewMenus && $searchForm->isFilterMenus()) {
                $paginators['menus'] = $this->getMenusPaginator($search, $searchForm->getPageMenus());
            }
            if ($canViewSlugs && $searchForm->isFilterSlugs()) {
                $paginators['slugs'] = $this->getSlugsPaginator($search, $searchForm->getPageSlugs());
            }
        }

        $viewData = [
            'searchForm' => $searchForm,
            'paginators' => $paginators,
            'canViewContents' => $canViewContents,
            'canViewTags' => $canViewTags,
            'canViewMenus' => $canViewMenus,
            'canViewSlugs' => $canViewSlugs,
            'urlGenerator' => $this->urlGenerator,
            'currentRoute' => $this->currentRoute,
        ];

        if ($this->isAjax()) {
            return $this->renderPartial('Search/_results', $viewData);
        }

        return $this->render('Search/index', $viewData);
    }

    private function getContentsPaginator(string $search, int $page): ActiveQueryPaginator
    {
        $query = Content::query()
            ->andWhere(['or',
                ['like', 'name', $search],
                ['like', '_extras', $search],
            ])
            ->orderBy(['name' => SORT_ASC]);

        return (new ActiveQueryPaginator($query))
            ->withPageSize($this->getListSize())
            ->withCurrentPage($page);
    }

    private function getTagsPaginator(string $search, int $page): ActiveQueryPaginator
    {
        $query = Tag::query()
            ->andWhere(['or',
                ['like', 'name', $search],
                ['like', '_extras', $search],
            ])
            ->orderBy(['left' => SORT_ASC]);

        return (new ActiveQueryPaginator($query))
            ->withPageSize($this->getListSize())
            ->withCurrentPage($page);
    }

    private function getMenusPaginator(string $search, int $page): ActiveQueryPaginator
    {
        $query = Menu::query()
            ->andWhere(['or',
                ['like', 'name', $search],
                ['like', 'route', $search],
            ])
            ->orderBy(['left' => SORT_ASC]);

        return (new ActiveQueryPaginator($query))
            ->withPageSize($this->getListSize())
            ->withCurrentPage($page);
    }

    private function getSlugsPaginator(string $search, int $page): ActiveQueryPaginator
    {
        $query = Slug::query()
            ->andWhere(['IS NOT', 'targetUrl', null])
            ->andWhere(['IS NOT', 'httpCode', null])
            ->andWhere(['or',
                ['like', 'path', $search],
                ['like', 'targetUrl', $search],
            ])
            ->orderBy(['id' => SORT_DESC]);

        return (new ActiveQueryPaginator($query))
            ->withPageSize($this->getListSize())
            ->withCurrentPage($page);
    }
}
