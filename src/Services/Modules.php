<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Bleet\Widgets\SidebarItem;
use Blackcube\Dboard\Interfaces\Oauth2Interface;
use Blackcube\Dboard\Interfaces\RbacInterface;
use Blackcube\Dboard\Interfaces\SidebarInterface;
use Blackcube\Dcore\Services\AddonService;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * The modules of the panel: the classes declared in 'blackcube/dboard' => 'modules', always on,
 * plus the active add-ons of the dcore registry, read on each call. A module carries the
 * interfaces of `Interfaces\` it needs and the panel reads them here: the RBAC classes to hold
 * in the storage, the permissions each OAuth2 population publishes, the sidebar items. An
 * inactive add-on contributes nothing.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Modules
{
    /**
     * @param list<class-string> $rbacClasses The RBAC classes of the panel itself, from the params
     * @param list<class-string> $modules The classes declared in the params, always on
     */
    public function __construct(
        private readonly AddonService $addonService,
        private readonly array $rbacClasses = [],
        private readonly array $modules = [],
    ) {}

    /**
     * The declared modules, then the active add-ons, each class once.
     *
     * @return list<class-string>
     */
    public function getClasses(): array
    {
        return array_values(array_unique(array_merge($this->modules, array_values($this->addonService->getActive()))));
    }

    /**
     * The RBAC classes of the panel plus the ones of the modules.
     *
     * @return list<class-string>
     */
    public function getRbacClasses(): array
    {
        $rbacClasses = $this->rbacClasses;
        foreach ($this->getClasses() as $class) {
            if (is_subclass_of($class, RbacInterface::class) === true) {
                $rbacClasses[] = $class::getRbac();
            }
        }
        return array_values(array_unique($rbacClasses));
    }

    /**
     * The permissions each OAuth2 population publishes, from the modules; modules
     * sharing a population pool them.
     *
     * @return array<string, string[]>
     */
    public function getPublishedScopes(): array
    {
        $publishedScopes = [];
        foreach ($this->getClasses() as $class) {
            if (is_subclass_of($class, Oauth2Interface::class) === true) {
                $population = $class::getPopulation();
                $known = [];
                if (isset($publishedScopes[$population]) === true) {
                    $known = $publishedScopes[$population];
                }
                $publishedScopes[$population] = array_values(array_unique(array_merge($known, $class::getScopes())));
            }
        }
        return $publishedScopes;
    }

    /**
     * The sidebar items of the modules, each module filtering by the permissions it needs.
     *
     * @return SidebarItem[]
     */
    public function getSidebarItems(
        ?TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        ManagerInterface $rbacManager,
        string $userId,
        ?string $currentRoute,
    ): array {
        $items = [];
        foreach ($this->getClasses() as $class) {
            if (is_subclass_of($class, SidebarInterface::class) === true) {
                $items = array_merge($items, $class::getSidebar(
                    $translator,
                    $urlGenerator,
                    $rbacManager,
                    $userId,
                    $currentRoute,
                ));
            }
        }
        return $items;
    }
}
