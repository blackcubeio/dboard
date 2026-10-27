<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dcore\Data\ActiveQueryPaginator;
use Blackcube\Handler\OutputType;
use Blackcube\Dboard\Models\Forms\PageForm;
use Blackcube\Dboard\Models\Forms\SearchForm;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\ActiveRecord\ActiveRecordInterface;

/**
 * Abstract index action for listing models with pagination and search.
 * Uses SearchForm and PageForm with lazy loading for handling request parameters.
 * Follows the standardized pipeline: setupAction() -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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
     * Returns the order by clause for the query.
     *
     * @return array<string, int> Column to sort direction mapping
     */
    protected function getOrderBy(): array
    {
        return [
            'left' => SORT_ASC,
        ];
    }

    /**
     * Returns the level offset for tree display.
     * Override in subclasses for node filtering support.
     */
    protected function getLevelOffset(): int
    {
        return 0;
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
        return '_list';
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
            $this->query = $this->query->andWhere([
                'like',
                $this->getSearchColumn(),
                $this->getSearchForm()->getSearch(),
            ]);
        }

        $this->paginator = (new ActiveQueryPaginator($this->query))
            ->withPageSize($this->getPageSize())
            ->withCurrentPage((int) $this->getPageForm()->getPage());
    }

    /**
     * Reads the current page items, eager-loading their relations to avoid N+1.
     * Returns null when the handler does not eager-load: the view then reads the
     * paginator itself (unchanged behaviour).
     *
     * @return array<int, mixed>|null The populated page items, or null
     */
    protected function eagerLoad(): ?array
    {
        return null;
    }

    /**
     * Loads records of the given class whose id is in $ids, indexed by id.
     * Single query via an IN clause — building block for eagerLoad() overrides.
     *
     * @param class-string $class The ActiveRecord class to load
     * @param array<int, int> $ids The ids to load
     * @return array<int, mixed> Records indexed by their id
     */
    protected function getIndexedRecords(string $class, array $ids): array
    {
        $records = [];
        if (empty($ids) === false) {
            $recordsQuery = $class::query()->andWhere([
                'in',
                'id',
                array_values($ids),
            ]);
            /** @var ActiveRecordInterface $record */
            foreach ($recordsQuery->each() as $record) {
                $records[$record->getId()] = $record;
            }
        }

        return $records;
    }

    /**
     * Prepares the output data for rendering.
     *
     * @return array{type: string, view: string, data: array<string, mixed>} Output configuration
     */
    protected function prepareOutputData(): array
    {
        $view = $this->isAjax() === true ? $this->getListView() : $this->getView();
        $viewPath = str_starts_with($view, '/') === true ? substr($view, 1) : $this->getViewPrefix() . '/' . $view;

        $outputData = [
            'type' => $this->isAjax() === true ? OutputType::Partial->value : OutputType::Render->value,
            'view' => $viewPath,
            'data' => [
                'paginator' => $this->paginator,
                'searchForm' => $this->getSearchForm(),
                'pageForm' => $this->getPageForm(),
                'urlGenerator' => $this->urlGenerator,
                'currentRoute' => $this->currentRoute,
                'levelOffset' => $this->getLevelOffset(),
            ],
        ];

        $items = $this->eagerLoad();
        if ($items !== null) {
            $outputData['data']['items'] = $items;
        }

        return $outputData;
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
