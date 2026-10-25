<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Widgets;

use Blackcube\Dboard\Components\Rbac;
use Blackcube\Dboard\DboardConfig;
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
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Sidebar extends Widget
{
    private ?Administrator $user = null;
    private ?string $currentRoute = null;
    private string $color = Bleet::COLOR_PRIMARY;
    private ?TranslatorInterface $translator = null;

    public function __construct(
        private ManagerInterface $rbacManager,
        private UrlGeneratorInterface $urlGenerator,
        private DboardConfig $dboardConfig,
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
        $items = [];

        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_SITE_DASHBOARD) === true) {
            $sidebarItem = Bleet::sidebarItem($this->translate('Dashboard'))
                ->outline('squares-2x2')
                ->url($this->urlGenerator->generate('dboard.dashboard'));
            if ($this->currentRoute === 'dboard.dashboard') {
                $sidebarItem = $sidebarItem->active();
            }
            $items[] = $sidebarItem;
        }

        $settingsChildren = [];
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_RBAC_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('RBAC'))
                ->url($this->urlGenerator->generate('dboard.rbac'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.rbac') === true) {
                $child = $child->active();
            }
            $settingsChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_LANGUAGE_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('Languages'))
                ->url($this->urlGenerator->generate('dboard.languages'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.languages') === true) {
                $child = $child->active();
            }
            $settingsChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_HOST_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('Hosts'))
                ->url($this->urlGenerator->generate('dboard.hosts'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.hosts') === true) {
                $child = $child->active();
            }
            $settingsChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_ADMINISTRATOR_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('Administrators'))
                ->url($this->urlGenerator->generate('dboard.administrators'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.administrators') === true) {
                $child = $child->active();
            }
            $settingsChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_PARAMETER_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('Parameters'))
                ->url($this->urlGenerator->generate('dboard.parameters'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.parameters') === true) {
                $child = $child->active();
            }
            $settingsChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_TYPE_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('Types'))
                ->url($this->urlGenerator->generate('dboard.types'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.types') === true) {
                $child = $child->active();
            }
            $settingsChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_ELASTICSCHEMA_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('Elastic Schemas'))
                ->url($this->urlGenerator->generate('dboard.elasticschemas'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.elasticschemas') === true) {
                $child = $child->active();
            }
            $settingsChildren[] = $child;
        }
        if (count($settingsChildren) > 0) {
            $items[] = Bleet::sidebarItem($this->translate('Settings'))
                ->outline('cog-6-tooth')
                ->toggleId('settings')
                ->children($settingsChildren);
        }

        $xeoChildren = [];
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_AUTHOR_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('Authors'))
                ->url($this->urlGenerator->generate('dboard.authors'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.authors') === true) {
                $child = $child->active();
            }
            $xeoChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_XEO_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('Organization'))
                ->url($this->urlGenerator->generate('dboard.xeo.organization'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.xeo.organization') === true) {
                $child = $child->active();
            }
            $xeoChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_XEO_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('Website'))
                ->url($this->urlGenerator->generate('dboard.xeo.website'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.xeo.website') === true) {
                $child = $child->active();
            }
            $xeoChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_LLMMENU_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('LLMs'))
                ->url($this->urlGenerator->generate('dboard.llmmenus'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.llmmenus') === true) {
                $child = $child->active();
            }
            $xeoChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_XEO_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('robots.txt'))
                ->url($this->urlGenerator->generate('dboard.xeo.robots'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.xeo.robots') === true) {
                $child = $child->active();
            }
            $xeoChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_XEO_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('Additional Sitemap'))
                ->url($this->urlGenerator->generate('dboard.xeo.sitemap'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.xeo.sitemap') === true) {
                $child = $child->active();
            }
            $xeoChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_XEO_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('Mappings'))
                ->url($this->urlGenerator->generate('dboard.xeo.mapping'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.xeo.mapping') === true) {
                $child = $child->active();
            }
            $xeoChildren[] = $child;
        }
        if (count($xeoChildren) > 0) {
            $items[] = Bleet::sidebarItem($this->translate('*EO'))
                ->outline('globe-alt')
                ->toggleId('xeo')
                ->children($xeoChildren);
        }

        $managementChildren = [];
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_MENU_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('Menus'))
                ->url($this->urlGenerator->generate('dboard.menus'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.menus') === true) {
                $child = $child->active();
            }
            $managementChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_TAG_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('Tags'))
                ->url($this->urlGenerator->generate('dboard.tags'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.tags') === true) {
                $child = $child->active();
            }
            $managementChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_CONTENT_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('Contents'))
                ->url($this->urlGenerator->generate('dboard.contents'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.contents') === true) {
                $child = $child->active();
            }
            $managementChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_SLUG_VIEW) === true) {
            $child = Bleet::sidebarItem($this->translate('URLs'))
                ->url($this->urlGenerator->generate('dboard.slugs'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.slugs') === true) {
                $child = $child->active();
            }
            $managementChildren[] = $child;
        }
        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_CONTENT_IMPORT) === true
            || $this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_TAG_IMPORT) === true) {
            $child = Bleet::sidebarItem($this->translate('Import'))
                ->url($this->urlGenerator->generate('dboard.import.step1'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.import') === true) {
                $child = $child->active();
            }
            $managementChildren[] = $child;
        }
        if (count($managementChildren) > 0) {
            $items[] = Bleet::sidebarItem($this->translate('Management'))
                ->outline('rectangle-stack')
                ->toggleId('management')
                ->children($managementChildren);
        }

        foreach ($this->dboardConfig->addons as $addon) {
            if (isset($addon['sidebar']) === true) {
                $addonElements = call_user_func(
                    $addon['sidebar'],
                    $this->translator,
                    $this->urlGenerator,
                    $this->rbacManager,
                    $userId,
                    $this->currentRoute,
                );
                $items = array_merge($items, $addonElements);
            }
        }

        if ($this->rbacManager->userHasPermission($userId, Rbac::PERMISSION_ADDON_VIEW) === true) {
            $sidebarItem = Bleet::sidebarItem($this->translate('Addons'))
                ->outline('puzzle-piece')
                ->url($this->urlGenerator->generate('dboard.addons'));
            if ($this->currentRoute !== null && str_starts_with($this->currentRoute, 'dboard.addons') === true) {
                $sidebarItem = $sidebarItem->active();
            }
            $items[] = $sidebarItem;
        }

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

    private function translate(string $message): string
    {
        $translatedMessage = $message;
        if ($this->translator !== null) {
            $translatedMessage = $this->translator->translate($message, category: 'dboard-common');
        }
        return $translatedMessage;
    }
}
