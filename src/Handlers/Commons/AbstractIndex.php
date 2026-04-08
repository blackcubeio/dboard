<?php

declare(strict_types=1);

/**
 * AbstractIndex.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Dboard\Enums\ListMode;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Forms\PageForm;
use Blackcube\Dboard\Models\Forms\SearchForm;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\ActiveRecord\ActiveQuery;

/**
 * Abstract index action for listing models with pagination and search.
 * Uses SearchForm and PageForm with lazy loading for handling request parameters.
 * Follows the standardized pipeline: setupAction() -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractIndex extends AbstractSessionHandler
{
    /**
     * @var SearchForm|null The search form instance
     */
    protected ?SearchForm $searchForm = null;

    /**
     * @var PageForm|null The page form instance
     */
    protected ?PageForm $pageForm = null;

    /**
     * @var ActiveQuery|null The query for fetching models
     */
    protected ?ActiveQuery $query = null;

    /**
     * @var ActiveQueryPaginator|null The paginator instance
     */
    protected ?ActiveQueryPaginator $paginator = null;

    /**
     * Returns the model class name.
     *
     * @return string Fully qualified class name of the ActiveRecord model
     */
    abstract protected function getModelClass(): string;

    /**
     * Returns the view prefix for templates.
     *
     * @return string The view prefix (e.g., 'Contents', 'Tags')
     */
    abstract protected function getViewPrefix(): string;

    /**
     * Returns the order by clause based on current mode.
     *
     * @return array<string, int> Column to sort direction mapping
     */
    protected function getOrderBy(): array
    {
        if ($this->getMode() === ListMode::Flat) {
            return ['dateUpdate' => SORT_DESC];
        }
        return ['left' => SORT_ASC];
    }

    /**
     * Returns the current display mode (tree or flat).
     * If a query parameter is present, saves it to session.
     * Otherwise, reads from session. Defaults to Tree.
     */
    protected function getMode(): ListMode
    {
        $sessionKey = 'dboard_list_mode_' . $this->getViewPrefix();
        $queryMode = $this->request->getQueryParams()['mode'] ?? '';

        if ($queryMode !== '') {
            $mode = ListMode::tryFrom($queryMode) ?? ListMode::Tree;
            $this->session->set($sessionKey, $mode->value);
            return $mode;
        }

        $sessionMode = $this->session->get($sessionKey);
        if ($sessionMode !== null) {
            return ListMode::tryFrom($sessionMode) ?? ListMode::Tree;
        }

        return ListMode::Tree;
    }

    /**
     * Returns the view name for full page rendering.
     * If prefixed with /, root-relative (/ stripped). Otherwise, prefixed with getViewPrefix().
     *
     * @return string The view name
     */
    protected function getView(): string
    {
        return 'index';
    }

    /**
     * Returns the view name for list partial rendering (AJAX).
     * If prefixed with /, root-relative (/ stripped). Otherwise, prefixed with getViewPrefix().
     *
     * @return string The view name
     */
    protected function getListView(): string
    {
        return $this->getMode() === ListMode::Flat ? '_list_flat' : '_list';
    }

    /**
     * Returns the column name used for search.
     * Override this method to change the search column.
     *
     * @return string The column name to search in
     */
    protected function getSearchColumn(): string
    {
        return 'name';
    }

    /**
     * Returns the page form instance with lazy loading.
     * Loads query parameters only (pagination links).
     *
     * @return PageForm The page form instance
     */
    protected function getPageForm(): PageForm
    {
        if ($this->pageForm === null) {
            $this->pageForm = new PageForm(translator: $this->translator);
            // Page comes from query params only (pagination links)
            $this->pageForm->load($this->request->getQueryParams(), '');
        }
        return $this->pageForm;
    }

    /**
     * Returns the search form instance with lazy loading.
     * Loads query parameters first, then body parameters if POST.
     *
     * @return SearchForm The search form instance
     */
    protected function getSearchForm(): SearchForm
    {
        if ($this->searchForm === null) {
            $this->searchForm = new SearchForm(translator: $this->translator);
            $this->searchForm->setScenario('simple');
            $this->searchForm->load($this->request->getQueryParams(), '');

            $bodyParams = $this->getBodyParams();
            if ($bodyParams !== null) {
                $this->searchForm->load($bodyParams);
            }
        }
        return $this->searchForm;
    }

    /**
     * Invokes the action and executes the pipeline.
     *
     * @param ServerRequestInterface $request The server request
     * @return ResponseInterface The response
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $this->setupAction();
        $outputData = $this->prepareOutputData();
        return $this->output($outputData);
    }

    /**
     * Sets up the action by building the query with search and pagination.
     *
     * @return void
     */
    protected function setupAction(): void
    {
        $modelClass = $this->getModelClass();
        $this->query = $modelClass::query()
            ->orderBy($this->getOrderBy());

        if ($this->getSearchForm()->getSearch() !== '') {
            $this->query = $this->query->andWhere(['like', $this->getSearchColumn(), $this->getSearchForm()->getSearch()]);
        }

        $this->paginator = (new ActiveQueryPaginator($this->query))
            ->withPageSize($this->getPageSize())
            ->withCurrentPage((int) $this->getPageForm()->getPage());
    }

    /**
     * Prepares the output data for rendering.
     *
     * @return array{type: string, view: string, data: array<string, mixed>} Output configuration
     */
    protected function prepareOutputData(): array
    {
        $view = $this->isAjax() ? $this->getListView() : $this->getView();
        $viewPath = str_starts_with($view, '/') ? substr($view, 1) : $this->getViewPrefix() . '/' . $view;

        return [
            'type' => $this->isAjax() ? OutputType::Partial->value : OutputType::Render->value,
            'view' => $viewPath,
            'data' => [
                'paginator' => $this->paginator,
                'searchForm' => $this->getSearchForm(),
                'pageForm' => $this->getPageForm(),
                'urlGenerator' => $this->urlGenerator,
                'currentRoute' => $this->currentRoute,
                'mode' => $this->getMode(),
            ],
        ];
    }

    /**
     * Dispatches the output based on the output data type.
     *
     * @param array{type: string, view: string, data: array<string, mixed>} $outputData The output configuration
     * @return ResponseInterface The response
     */
    protected function output(array $outputData): ResponseInterface
    {
        return match ($outputData['type']) {
            'render' => $this->render($outputData['view'], $outputData['data']),
            'partial' => $this->renderPartial($outputData['view'], $outputData['data']),
        };
    }
}