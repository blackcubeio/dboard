<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeFormModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Length;

/**
 * Search form model for handling search and filter parameters.
 * Use scenario 'simple' for basic search, 'global' for multi-entity search with filters.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class SearchForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-common';

    /**
     * @var string Search term
     */
    protected string $search = '';

    /**
     * @var bool Filter for contents (global search)
     */
    protected bool $filterContents = false;

    /**
     * @var bool Filter for tags (global search)
     */
    protected bool $filterTags = false;

    /**
     * @var bool Filter for menus (global search)
     */
    protected bool $filterMenus = false;

    /**
     * @var bool Filter for slugs (global search)
     */
    protected bool $filterSlugs = false;

    /**
     * @var int Current page of the contents results (global search)
     */
    protected int $pageContents = 1;

    /**
     * @var int Current page of the tags results (global search)
     */
    protected int $pageTags = 1;

    /**
     * @var int Current page of the menus results (global search)
     */
    protected int $pageMenus = 1;

    /**
     * @var int Current page of the slugs results (global search)
     */
    protected int $pageSlugs = 1;

    /**
     * Sets the search term.
     *
     * @param string $search The search term
     * @return void
     */
    #[Bridge]
    public function setSearch(string $search): void
    {
        $this->search = $search;
    }

    /**
     * Returns the search term.
     *
     * @return string The search term
     */
    #[Bridge]
    public function getSearch(): string
    {
        return $this->search;
    }

    /**
     * Whether a search filter is active.
     *
     * @return bool True if a non-empty search term is set
     */
    public function isSearch(): bool
    {
        return $this->search !== '';
    }

    /**
     * Sets the contents filter.
     *
     * @param bool $filterContents The filter value
     * @return void
     */
    #[Bridge]
    public function setFilterContents(bool $filterContents): void
    {
        $this->filterContents = $filterContents;
    }

    /**
     * Returns the contents filter value.
     *
     * @return bool The filter value
     */
    #[Bridge]
    public function isFilterContents(): bool
    {
        return $this->filterContents;
    }

    /**
     * Sets the tags filter.
     *
     * @param bool $filterTags The filter value
     * @return void
     */
    #[Bridge]
    public function setFilterTags(bool $filterTags): void
    {
        $this->filterTags = $filterTags;
    }

    /**
     * Returns the tags filter value.
     *
     * @return bool The filter value
     */
    #[Bridge]
    public function isFilterTags(): bool
    {
        return $this->filterTags;
    }

    /**
     * Sets the menus filter.
     *
     * @param bool $filterMenus The filter value
     * @return void
     */
    #[Bridge]
    public function setFilterMenus(bool $filterMenus): void
    {
        $this->filterMenus = $filterMenus;
    }

    /**
     * Returns the menus filter value.
     *
     * @return bool The filter value
     */
    #[Bridge]
    public function isFilterMenus(): bool
    {
        return $this->filterMenus;
    }

    /**
     * Sets the slugs filter.
     *
     * @param bool $filterSlugs The filter value
     * @return void
     */
    #[Bridge]
    public function setFilterSlugs(bool $filterSlugs): void
    {
        $this->filterSlugs = $filterSlugs;
    }

    /**
     * Returns the slugs filter value.
     *
     * @return bool The filter value
     */
    #[Bridge]
    public function isFilterSlugs(): bool
    {
        return $this->filterSlugs;
    }

    /**
     * Sets the current page of the contents results.
     *
     * @param int $pageContents The page number
     * @return void
     */
    #[Bridge]
    public function setPageContents(int $pageContents): void
    {
        $this->pageContents = $pageContents;
    }

    /**
     * Returns the current page of the contents results.
     *
     * @return int The page number
     */
    #[Bridge]
    public function getPageContents(): int
    {
        return $this->pageContents;
    }

    /**
     * Sets the current page of the tags results.
     *
     * @param int $pageTags The page number
     * @return void
     */
    #[Bridge]
    public function setPageTags(int $pageTags): void
    {
        $this->pageTags = $pageTags;
    }

    /**
     * Returns the current page of the tags results.
     *
     * @return int The page number
     */
    #[Bridge]
    public function getPageTags(): int
    {
        return $this->pageTags;
    }

    /**
     * Sets the current page of the menus results.
     *
     * @param int $pageMenus The page number
     * @return void
     */
    #[Bridge]
    public function setPageMenus(int $pageMenus): void
    {
        $this->pageMenus = $pageMenus;
    }

    /**
     * Returns the current page of the menus results.
     *
     * @return int The page number
     */
    #[Bridge]
    public function getPageMenus(): int
    {
        return $this->pageMenus;
    }

    /**
     * Sets the current page of the slugs results.
     *
     * @param int $pageSlugs The page number
     * @return void
     */
    #[Bridge]
    public function setPageSlugs(int $pageSlugs): void
    {
        $this->pageSlugs = $pageSlugs;
    }

    /**
     * Returns the current page of the slugs results.
     *
     * @return int The page number
     */
    #[Bridge]
    public function getPageSlugs(): int
    {
        return $this->pageSlugs;
    }

    /**
     * Loads data into the form.
     * Resets checkbox values before loading (unchecked = not sent).
     * For 'global' scenario, enables all filters if none are selected.
     *
     * @param mixed $submittedValues The submitted values to load
     * @param string|null $scope The scope for loading
     * @return bool Whether loading was successful
     */
    public function load(mixed $submittedValues, ?string $scope = null): bool
    {
        $this->filterContents = false;
        $this->filterTags = false;
        $this->filterMenus = false;
        $this->filterSlugs = false;

        $parentLoad = parent::load($submittedValues, $scope);

        if ($this->getScenario() === 'global') {
            if ($this->filterContents === false &&
                $this->filterTags === false &&
                $this->filterMenus === false &&
                $this->filterSlugs === false) {
                $this->filterContents = true;
                $this->filterTags = true;
                $this->filterMenus = true;
                $this->filterSlugs = true;
            }
        }

        return $parentLoad;
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        return [
            'simple' => [
                'search',
            ],
            'global' => [
                'search',
                'filterContents',
                'filterTags',
                'filterMenus',
                'filterSlugs',
                'pageContents',
                'pageTags',
                'pageMenus',
                'pageSlugs',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            'search' => [
                new Length(max: 255),
            ],
            'filterContents' => [
                new BooleanValue(),
            ],
            'filterTags' => [
                new BooleanValue(),
            ],
            'filterMenus' => [
                new BooleanValue(),
            ],
            'filterSlugs' => [
                new BooleanValue(),
            ],
            'pageContents' => [
                new Integer(min: 1),
            ],
            'pageTags' => [
                new Integer(min: 1),
            ],
            'pageMenus' => [
                new Integer(min: 1),
            ],
            'pageSlugs' => [
                new Integer(min: 1),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRawLabels(): array
    {
        return [
            'search' => 'Search',
            'filterContents' => 'Contents',
            'filterTags' => 'Tags',
            'filterMenus' => 'Menus',
            'filterSlugs' => 'URLs',
            'pageContents' => 'Page',
            'pageTags' => 'Page',
            'pageMenus' => 'Page',
            'pageSlugs' => 'Page',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyPlaceholders(): array
    {
        return [
            'search' => 'Rechercher...',
        ];
    }
}
