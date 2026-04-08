<?php

declare(strict_types=1);

/**
 * RouteHelper.php
 *
 * @copyright 2010-2026 Blackcube
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
 */
final class RouteHelper
{
    public function __construct(
        private RouteCollectionInterface $routeCollection,
        private DboardConfig $dboardConfig,
        private TranslatorInterface $translator,
    ) {
    }

    private static $instance = null;
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
        return array_merge($this->getCmsRoutes(), $this->getRegularRoutes());
    }

    /**
     * CMS routes: contents + tags.
     *
     * @return array<string, array<string, string>>
     */
    public function getCmsRoutes(): array
    {
        return array_merge($this->getContentRoutes(), $this->getTagRoutes());
    }

    /**
     * Content routes.
     *
     * @param bool $byId If true, index by model ID instead of route string
     * @param ActiveQuery|null $query Custom query. Defaults to contents with slug.
     * @return array<string, array<string|int, string>>
     */
    public function getContentRoutes(bool $byId = false, ?ActiveQuery $query = null): array
    {
        $options = [];
        $query = $query ?? Content::query()->andWhere(['is not', 'slugId', null]);
        foreach ($query->each() as $content) {
            $prefix = str_repeat('-', $content->getLevel() - 1);
            $label = $prefix . ' ' . ($content->getName() ?? $this->translator->translate('(no name)', category: 'dboard-common'));
            if ($content->getLanguageId() !== null) {
                $label .= ' (' . $content->getLanguageId() . ')';
            }
            $key = $byId ? $content->getId() : Element::createFromModel($content)->toRoute();
            $options[$key] = $label;
        }
        return [
            $this->translator->translate('CMS contents', category: 'dboard-common') => $options,
        ];
    }

    /**
     * Tag routes.
     *
     * @param bool $byId If true, index by model ID instead of route string
     * @param ActiveQuery|null $query Custom query. Defaults to tags with slug.
     * @return array<string, array<string|int, string>>
     */
    public function getTagRoutes(bool $byId = false, ?ActiveQuery $query = null): array
    {
        $options = [];
        $query = $query ?? Tag::query()->andWhere(['is not', 'slugId', null]);
        foreach ($query->each() as $tag) {
            $prefix = str_repeat('-', $tag->getLevel() - 1);
            $label = $prefix . ' ' . $tag->getName();
            if ($tag->getLanguageId() !== null) {
                $label .= ' (' . $tag->getLanguageId() . ')';
            }
            $key = $byId ? $tag->getId() : Element::createFromModel($tag)->toRoute();
            $options[$key] = $label;
        }
        return [
            $this->translator->translate('CMS tags', category: 'dboard-common') => $options,
        ];
    }

    /**
     * Menu routes.
     *
     * @param bool $byId If true, index by model ID instead of route string
     * @param ActiveQuery|null $query Custom query. Defaults to all menus in natural order.
     * @return array<string, array<string|int, string>>
     */
    public function getMenuRoutes(bool $byId = false, ?ActiveQuery $query = null): array
    {
        $options = [];
        $query = $query ?? Menu::query()->natural();
        foreach ($query->each() as $menu) {
            $prefix = str_repeat('-', $menu->getLevel() - 1);
            $label = $prefix . ' ' . $menu->getName();
            $key = $byId ? $menu->getId() : $menu->getId();
            $options[$key] = $label;
        }
        return [
            $this->translator->translate('CMS menus', category: 'dboard-common') => $options,
        ];
    }

    /**
     * LlmMenu routes.
     *
     * @param bool $byId If true, index by model ID instead of route string
     * @param ActiveQuery|null $query Custom query. Defaults to all LLM menus in natural order.
     * @return array<string, array<string|int, string>>
     */
    public function getLlmMenuRoutes(bool $byId = false, ?ActiveQuery $query = null): array
    {
        $options = [];
        $query = $query ?? LlmMenu::query()->natural();
        foreach ($query->each() as $llmMenu) {
            $prefix = str_repeat('-', $llmMenu->getLevel() - 1);
            $label = $prefix . ' ' . $llmMenu->getName();
            $key = $byId ? $llmMenu->getId() : $llmMenu->getId();
            $options[$key] = $label;
        }
        return [
            $this->translator->translate('LLM menus', category: 'dboard-common') => $options,
        ];
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

            if ($name === null) {
                continue;
            }

            if (str_starts_with($pattern, $this->dboardConfig->routePrefix) || str_starts_with($name, 'dboard.')) {
                continue;
            }

            $options[$name] = $name . ' (' . $pattern . ')';
        }
        ksort($options);
        return [
            $this->translator->translate('Standard pages', category: 'dboard-common') => $options,
        ];
    }
}
