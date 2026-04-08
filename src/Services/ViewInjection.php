<?php

declare(strict_types=1);

/**
 * ViewInjection.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dcore\Interfaces\SsrRouteProviderInterface;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Strings\Inflector;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\CommonParametersInjectionInterface;

final class ViewInjection implements CommonParametersInjectionInterface
{
    private static ?string $userId = null;
    private static ?ManagerInterface $rbacManager = null;

    /**
     * @var array<string, string> SSR route options (route => label)
     */
    private static array $ssrRoutes = [];

    public function __construct(
        ManagerInterface $rbacManager,
        private readonly TranslatorInterface $translator,
        ?SsrRouteProviderInterface $ssrRouteProvider = null,
    ) {
        self::$rbacManager = $rbacManager;
        if ($ssrRouteProvider !== null) {
            self::setSsrRoutes($ssrRouteProvider->getAvailableRoutes());
        }
    }

    public static function setUserId(?string $userId): void
    {
        self::$userId = $userId;
    }

    /**
     * Set SSR route options from SsrRouteProviderInterface.
     *
     * @param string[]|null $routes Raw route names
     */
    public static function setSsrRoutes(?array $routes): void
    {
        if (is_array($routes) && !empty($routes)) {
            $inflector = new Inflector();
            self::$ssrRoutes = [];
            foreach ($routes as $route) {
                $name = str_replace(['-', '_'], ' ', $route);
                self::$ssrRoutes[$route] = $inflector->toWords($name);
            }
            asort(self::$ssrRoutes);
        }
    }

    /**
     * Check if the current user has a specific permission.
     *
     * @param string $permission The permission to check
     * @return bool
     */
    public static function userCan(string $permission): bool
    {
        if (self::$userId === null || self::$rbacManager === null) {
            return false;
        }
        return self::$rbacManager->userHasPermission(self::$userId, $permission);
    }

    public function getCommonParameters(): array
    {
        $administrator = null;
        if (self::$userId !== null) {
            $administrator = Administrator::query()
                ->where(['id' => (int) self::$userId])
                ->one();
        }

        return [
            'administrator' => $administrator,
            'userCan' => fn(string $permission): bool => self::userCan($permission),
            'ssrRoutes' => self::$ssrRoutes,
            'translator' => $this->translator,
        ];
    }
}
