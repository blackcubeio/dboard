<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Helpers;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dcore\Helpers\Element;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\LlmMenu;
use Blackcube\Dcore\Models\Menu;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Injector\Injector;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Router\RouteCollectionInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * Helper for building route options for select dropdowns.
 *
 * Provides grouped route options by category:
 * - contentRoutes: CMS contents with slug
 * - tagRoutes: CMS tags with slug
 * - menuRoutes: CMS menus
 * - llmMenuRoutes: LLM menus
 * - regularRoutes: standard application routes (non-dboard)
 * - cmsRoutes: contents + tags
 * - routes: contents + tags + regular
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class RouteHelper
{
    private static $instance = null;

    private ?array $routesCache = null;
    private ?array $cmsRoutesCache = null;
    /** @var array<string, array<string, string>>|null */
    private ?array $contentRoutesCache = null;
    /** @var array<string, array<int, string>>|null */
    private ?array $contentOptionsCache = null;
    /** @var array<string, array<string, string>>|null */
    private ?array $tagRoutesCache = null;
    /** @var array<string, array<int, string>>|null */
    private ?array $tagOptionsCache = null;
    /** @var array<string, array<int, string>>|null */
    private ?array $menuOptionsCache = null;
    /** @var array<string, array<int, string>>|null */
    private ?array $llmMenuOptionsCache = null;

    public function __construct(
        private RouteCollectionInterface $routeCollection,
        private DboardConfig $dboardConfig,
        private TranslatorInterface $translator,
    ) {
    }

    public static function create(): static
    {
        if (static::$instance === null) {
            static::$instance = Injector::get(static::class);
        }
        return static::$instance;
    }

    /**
     * All routes: contents + tags + regular.
     *
     * @return array<string, array<string, string>>
     */
    public function getRoutes(): array
    {
        return $this->routesCache ??= array_merge($this->getCmsRoutes(), $this->getRegularRoutes());
    }

    /**
     * CMS routes: contents + tags.
     *
     * @return array<string, array<string, string>>
     */
    public function getCmsRoutes(): array
    {
        return $this->cmsRoutesCache ??= array_merge($this->getContentRoutes(), $this->getTagRoutes());
    }

    /**
     * Content routes: contents with a slug, keyed by route string.
     *
     * @param ActiveQuery|null $query Custom query. Defaults to contents with slug.
     * @return array<string, array<string, string>>
     */
    public function getContentRoutes(?ActiveQuery $query = null): array
    {
        $cacheable = $query === null;
        if ($cacheable === true && $this->contentRoutesCache !== null) {
            $contentRoutes = $this->contentRoutesCache;
        } else {
            $contentsQuery = $query ?? Content::query()->andWhere([
                'is not',
                'slugId',
                null,
            ]);
            $options = [];
            /** @var Content $content */
            foreach ($contentsQuery->each() as $content) {
                $options[Element::createFromModel($content)->toRoute()] = $this->buildContentLabel($content);
            }
            $contentRoutes = [
                $this->translator->translate('CMS contents', category: 'dboard-common') => $options,
            ];
            if ($cacheable === true) {
                $this->contentRoutesCache = $contentRoutes;
            }
        }
        return $contentRoutes;
    }

    /**
     * Content options: every content, keyed by content ID.
     *
     * @param ActiveQuery|null $query Custom query. Defaults to all contents.
     * @return array<string, array<int, string>>
     */
    public function getContentOptions(?ActiveQuery $query = null): array
    {
        $cacheable = $query === null;
        if ($cacheable === true && $this->contentOptionsCache !== null) {
            $contentOptions = $this->contentOptionsCache;
        } else {
            $contentsQuery = $query ?? Content::query();
            $options = [];
            /** @var Content $content */
            foreach ($contentsQuery->each() as $content) {
                $options[$content->getId()] = $this->buildContentLabel($content);
            }
            $contentOptions = [
                $this->translator->translate('CMS contents', category: 'dboard-common') => $options,
            ];
            if ($cacheable === true) {
                $this->contentOptionsCache = $contentOptions;
            }
        }
        return $contentOptions;
    }

    /**
     * Tag routes: tags with a slug, keyed by route string.
     *
     * @param ActiveQuery|null $query Custom query. Defaults to tags with slug.
     * @return array<string, array<string, string>>
     */
    public function getTagRoutes(?ActiveQuery $query = null): array
    {
        $cacheable = $query === null;
        if ($cacheable === true && $this->tagRoutesCache !== null) {
            $tagRoutes = $this->tagRoutesCache;
        } else {
            $tagsQuery = $query ?? Tag::query()->andWhere([
                'is not',
                'slugId',
                null,
            ]);
            $options = [];
            /** @var Tag $tag */
            foreach ($tagsQuery->each() as $tag) {
                $options[Element::createFromModel($tag)->toRoute()] = $this->buildTagLabel($tag);
            }
            $tagRoutes = [
                $this->translator->translate('CMS tags', category: 'dboard-common') => $options,
            ];
            if ($cacheable === true) {
                $this->tagRoutesCache = $tagRoutes;
            }
        }
        return $tagRoutes;
    }

    /**
     * Tag options: every tag, keyed by tag ID.
     *
     * @param ActiveQuery|null $query Custom query. Defaults to all tags.
     * @return array<string, array<int, string>>
     */
    public function getTagOptions(?ActiveQuery $query = null): array
    {
        $cacheable = $query === null;
        if ($cacheable === true && $this->tagOptionsCache !== null) {
            $tagOptions = $this->tagOptionsCache;
        } else {
            $tagsQuery = $query ?? Tag::query();
            $options = [];
            /** @var Tag $tag */
            foreach ($tagsQuery->each() as $tag) {
                $options[$tag->getId()] = $this->buildTagLabel($tag);
            }
            $tagOptions = [
                $this->translator->translate('CMS tags', category: 'dboard-common') => $options,
            ];
            if ($cacheable === true) {
                $this->tagOptionsCache = $tagOptions;
            }
        }
        return $tagOptions;
    }

    /**
     * Menu options: every menu, keyed by menu ID.
     *
     * @param ActiveQuery|null $query Custom query. Defaults to all menus in natural order.
     * @return array<string, array<int, string>>
     */
    public function getMenuOptions(?ActiveQuery $query = null): array
    {
        $cacheable = $query === null;
        if ($cacheable === true && $this->menuOptionsCache !== null) {
            $menuOptions = $this->menuOptionsCache;
        } else {
            $menusQuery = $query ?? Menu::query()->natural();
            $options = [];
            /** @var Menu $menu */
            foreach ($menusQuery->each() as $menu) {
                $options[$menu->getId()] = str_repeat('-', $menu->getLevel() - 1) . ' ' . $menu->getName();
            }
            $menuOptions = [
                $this->translator->translate('CMS menus', category: 'dboard-common') => $options,
            ];
            if ($cacheable === true) {
                $this->menuOptionsCache = $menuOptions;
            }
        }
        return $menuOptions;
    }

    /**
     * LLM menu options: every LLM menu, keyed by LLM menu ID.
     *
     * @param ActiveQuery|null $query Custom query. Defaults to all LLM menus in natural order.
     * @return array<string, array<int, string>>
     */
    public function getLlmMenuOptions(?ActiveQuery $query = null): array
    {
        $cacheable = $query === null;
        if ($cacheable === true && $this->llmMenuOptionsCache !== null) {
            $llmMenuOptions = $this->llmMenuOptionsCache;
        } else {
            $llmMenusQuery = $query ?? LlmMenu::query()->natural();
            $options = [];
            /** @var LlmMenu $llmMenu */
            foreach ($llmMenusQuery->each() as $llmMenu) {
                $options[$llmMenu->getId()] = str_repeat('-', $llmMenu->getLevel() - 1) . ' ' . $llmMenu->getName();
            }
            $llmMenuOptions = [
                $this->translator->translate('LLM menus', category: 'dboard-common') => $options,
            ];
            if ($cacheable === true) {
                $this->llmMenuOptionsCache = $llmMenuOptions;
            }
        }
        return $llmMenuOptions;
    }

    /**
     * Standard (non-CMS) routes.
     *
     * @return array<string, array<string, string>>
     */
    public function getRegularRoutes(): array
    {
        $options = [];
        foreach ($this->routeCollection->getRoutes() as $route) {
            $name = $route->getData('name');
            $pattern = $route->getData('pattern') ?? '';

            if ($name !== null && str_starts_with($pattern, $this->dboardConfig->routePrefix) === false && str_starts_with($name, 'dboard.') === false) {
                $options[$name] = $name . ' (' . $pattern . ')';
            }
        }
        ksort($options);
        return [
            $this->translator->translate('Standard pages', category: 'dboard-common') => $options,
        ];
    }

    private function buildContentLabel(Content $content): string
    {
        $prefix = str_repeat('-', $content->getLevel() - 1);
        $label = $prefix . ' ' . ($content->getName() ?? $this->translator->translate('(no name)', category: 'dboard-common'));
        if ($content->getLanguageId() !== null) {
            $label .= ' (' . $content->getLanguageId() . ')';
        }
        return $label;
    }

    private function buildTagLabel(Tag $tag): string
    {
        $prefix = str_repeat('-', $tag->getLevel() - 1);
        $label = $prefix . ' ' . $tag->getName();
        if ($tag->getLanguageId() !== null) {
            $label .= ' (' . $tag->getLanguageId() . ')';
        }
        return $label;
    }
}
