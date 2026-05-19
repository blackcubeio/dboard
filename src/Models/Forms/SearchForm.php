<?php

declare(strict_types=1);

/**
 * SearchForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\Length;

/**
 * Search form model for handling search and filter parameters.
 * Use scenario 'simple' for basic search, 'global' for multi-entity search with filters.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class SearchForm extends BridgeFormModel
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
     * Loads data into the form.
     * Resets checkbox values before loading (unchecked = not sent).
     * For 'global' scenario, enables all filters if none are selected.
     *
     * @param mixed $data The data to load
     * @param string|null $scope The scope for loading
     * @return bool Whether loading was successful
     */
    public function load(mixed $data, ?string $scope = null): bool
    {
        // Reset checkboxes before load (unchecked = not sent)
        $this->filterContents = false;
        $this->filterTags = false;
        $this->filterMenus = false;
        $this->filterSlugs = false;

        $parentLoad = parent::load($data, $scope);

        // For 'global' scenario: if no filter is set, enable all filters
        if ($this->getScenario() === 'global') {
            if (!$this->filterContents &&
                !$this->filterTags &&
                !$this->filterMenus &&
                !$this->filterSlugs) {
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
            'simple' => ['search'],
            'global' => ['search', 'filterContents', 'filterTags', 'filterMenus', 'filterSlugs'],
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
