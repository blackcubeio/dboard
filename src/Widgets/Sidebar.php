<?php

declare(strict_types=1);

/**
 * Sidebar.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Widgets;

use Blackcube\Dboard\Components\Rbac;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Widgets\SidebarItem;
use Yiisoft\Html\Html;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Widget\Widget;

/**
 * Sidebar widget for Dboard admin panel with RBAC integration
 *
 * Usage:
 *   Widgets::sidebar()
 *       ->user($administrator)
 *       ->currentRoute('dboard.dashboard')
 *       ->render()
 */
final class Sidebar extends Widget
{
    private ?Administrator $user = null;
    private ?string $currentRoute = null;
    private string $color = Bleet::COLOR_PRIMARY;
    private ?TranslatorInterface $translator = null;

    public function __construct(
        private ManagerInterface $rbacManager,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function translator(TranslatorInterface $translator): self
    {
        $new = clone $this;
        $new->translator = $translator;
        return $new;
    }

    public function color(string $color): self
    {
        $new = clone $this;
        $new->color = $color;
        return $new;
    }

    public function primary(): self
    {
        return $this->color(Bleet::COLOR_PRIMARY);
    }

    public function secondary(): self
    {
        return $this->color(Bleet::COLOR_SECONDARY);
    }

    public function success(): self
    {
        return $this->color(Bleet::COLOR_SUCCESS);
    }

    public function danger(): self
    {
        return $this->color(Bleet::COLOR_DANGER);
    }

    public function warning(): self
    {
        return $this->color(Bleet::COLOR_WARNING);
    }

    public function info(): self
    {
        return $this->color(Bleet::COLOR_INFO);
    }

    public function accent(): self
    {
        return $this->color(Bleet::COLOR_ACCENT);
    }

    public function user(Administrator $user): self
    {
        $new = clone $this;
        $new->user = $user;
        return $new;
    }

    public function currentRoute(string $route): self
    {
        $new = clone $this;
        $new->currentRoute = $route;
        return $new;
    }

    public function render(): string
    {
        if ($this->user === null) {
            throw new \RuntimeException('Sidebar requires user() to be set');
        }

        $userId = (string) $this->user->getId();
        $t = fn(string $message) => $this->translator?->translate($message, category: 'dboard-common') ?? $message;
        $items = [];

        // Dashboard
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_SITE_DASHBOARD)) {
            $item = Bleet::sidebarItem($t('Dashboard'))
                ->outline('squares-2x2')
                ->url($this->urlGenerator->generate('dboard.dashboard'));
            if ($this->currentRoute === 'dboard.dashboard') {
                $item = $item->active();
            }
            $items[] = $item;
        }

        // Settings group
        $settingsChildren = [];
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_RBAC_VIEW)) {
            $child = Bleet::sidebarItem($t('RBAC'))
                ->url($this->urlGenerator->generate('dboard.rbac'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.rbac')) {
                $child = $child->active();
            }
            $settingsChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_LANGUAGE_VIEW)) {
            $child = Bleet::sidebarItem($t('Languages'))
                ->url($this->urlGenerator->generate('dboard.languages'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.languages')) {
                $child = $child->active();
            }
            $settingsChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_HOST_VIEW)) {
            $child = Bleet::sidebarItem($t('Hosts'))
                ->url($this->urlGenerator->generate('dboard.hosts'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.hosts')) {
                $child = $child->active();
            }
            $settingsChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_ADMINISTRATOR_VIEW)) {
            $child = Bleet::sidebarItem($t('Administrators'))
                ->url($this->urlGenerator->generate('dboard.administrators'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.administrators')) {
                $child = $child->active();
            }
            $settingsChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_PARAMETER_VIEW)) {
            $child = Bleet::sidebarItem($t('Parameters'))
                ->url($this->urlGenerator->generate('dboard.parameters'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.parameters')) {
                $child = $child->active();
            }
            $settingsChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_TYPE_VIEW)) {
            $child = Bleet::sidebarItem($t('Types'))
                ->url($this->urlGenerator->generate('dboard.types'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.types')) {
                $child = $child->active();
            }
            $settingsChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_ELASTICSCHEMA_VIEW)) {
            $child = Bleet::sidebarItem($t('Elastic Schemas'))
                ->url($this->urlGenerator->generate('dboard.elasticschemas'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.elasticschemas')) {
                $child = $child->active();
            }
            $settingsChildren[] = $child;
        }
        if (count($settingsChildren) > 0) {
            $items[] = Bleet::sidebarItem($t('Settings'))
                ->outline('cog-6-tooth')
                ->toggleId('settings')
                ->children($settingsChildren);
        }

        // XEO Site group
        $xeoChildren = [];
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_AUTHOR_VIEW)) {
            $child = Bleet::sidebarItem($t('Authors'))
                ->url($this->urlGenerator->generate('dboard.authors'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.authors')) {
                $child = $child->active();
            }
            $xeoChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_XEO_VIEW)) {
            $child = Bleet::sidebarItem($t('Organization'))
                ->url($this->urlGenerator->generate('dboard.xeo.organization'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.xeo.organization')) {
                $child = $child->active();
            }
            $xeoChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_XEO_VIEW)) {
            $child = Bleet::sidebarItem($t('Website'))
                ->url($this->urlGenerator->generate('dboard.xeo.website'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.xeo.website')) {
                $child = $child->active();
            }
            $xeoChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_LLMMENU_VIEW)) {
            $child = Bleet::sidebarItem($t('LLMs'))
                ->url($this->urlGenerator->generate('dboard.llmmenus'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.llmmenus')) {
                $child = $child->active();
            }
            $xeoChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_XEO_VIEW)) {
            $child = Bleet::sidebarItem($t('robots.txt'))
                ->url($this->urlGenerator->generate('dboard.xeo.robots'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.xeo.robots')) {
                $child = $child->active();
            }
            $xeoChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_XEO_VIEW)) {
            $child = Bleet::sidebarItem($t('Additional Sitemap'))
                ->url($this->urlGenerator->generate('dboard.xeo.sitemap'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.xeo.sitemap')) {
                $child = $child->active();
            }
            $xeoChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_XEO_VIEW)) {
            $child = Bleet::sidebarItem($t('Mappings'))
                ->url($this->urlGenerator->generate('dboard.xeo.mapping'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.xeo.mapping')) {
                $child = $child->active();
            }
            $xeoChildren[] = $child;
        }
        if (count($xeoChildren) > 0) {
            $items[] = Bleet::sidebarItem($t('*EO'))
                ->outline('globe-alt')
                ->toggleId('xeo')
                ->children($xeoChildren);
        }

        // Management group
        $managementChildren = [];
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_MENU_VIEW)) {
            $child = Bleet::sidebarItem($t('Menus'))
                ->url($this->urlGenerator->generate('dboard.menus'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.menus')) {
                $child = $child->active();
            }
            $managementChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_TAG_VIEW)) {
            $child = Bleet::sidebarItem($t('Tags'))
                ->url($this->urlGenerator->generate('dboard.tags'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.tags')) {
                $child = $child->active();
            }
            $managementChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_CONTENT_VIEW)) {
            $child = Bleet::sidebarItem($t('Contents'))
                ->url($this->urlGenerator->generate('dboard.contents'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.contents')) {
                $child = $child->active();
            }
            $managementChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_SLUG_VIEW)) {
            $child = Bleet::sidebarItem($t('URLs'))
                ->url($this->urlGenerator->generate('dboard.slugs'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.slugs')) {
                $child = $child->active();
            }
            $managementChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_CONTENT_IMPORT)
            || $this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_TAG_IMPORT)) {
            $child = Bleet::sidebarItem($t('Import'))
                ->url($this->urlGenerator->generate('dboard.import.step1'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.import')) {
                $child = $child->active();
            }
            $managementChildren[] = $child;
        }
        if (count($managementChildren) > 0) {
            $items[] = Bleet::sidebarItem($t('Management'))
                ->outline('rectangle-stack')
                ->toggleId('management')
                ->children($managementChildren);
        }

        /*/
        // Plugins group
        $pluginsChildren = [];
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_PLUGIN_VIEW)) {
            $child = Bleet::sidebarItem('Configuration')
                ->url($this->urlGenerator->generate('dboard.plugins'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.plugins')) {
                $child = $child->active();
            }
            $pluginsChildren[] = $child;
        }
        if (count($pluginsChildren) > 0) {
            $items[] = Bleet::sidebarItem('Plugins')
                ->outline('puzzle-piece')
                ->toggleId('plugins')
                ->children($pluginsChildren);
        }
        /**/

        $logoIcon = Html::div(Bleet::svg()->logo('blackcube')->addClass('size-6'))
            ->class('bg-black', 'rounded-md', 'p-1', 'text-white')
            ->encode(false)
            ->render();
        $logoText = Html::span('DBOARD')
            ->class('font-semibold', 'text-primary-800')
            ->render();
        $logoHtml = Html::div($logoIcon . $logoText)
            ->class('flex', 'items-center', 'gap-2')
            ->encode(false)
            ->render();

        return Bleet::sidebar()
            ->logo($logoHtml)
            ->encode(false)
            ->items($items)
            ->color($this->color)
            ->render();
    }

}
