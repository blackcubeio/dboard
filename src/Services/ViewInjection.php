<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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

/**
 * Parameters shared by every view of the admin panel: current administrator, permissions and SSR routes.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ViewInjection implements CommonParametersInjectionInterface
{
    private static ?string $userId = null;
    private static ?Administrator $administrator = null;
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

    public static function setAdministrator(?Administrator $administrator): void
    {
        self::$administrator = $administrator;
    }

    /**
     * Set SSR route options from SsrRouteProviderInterface.
     *
     * @param string[]|null $routes Raw route names
     */
    public static function setSsrRoutes(?array $routes): void
    {
        if (is_array($routes) === true && empty($routes) === false) {
            $inflector = new Inflector();
            self::$ssrRoutes = [];
            foreach ($routes as $route) {
                $name = str_replace([
                    '-',
                    '_',
                ], ' ', $route);
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
        return self::$userId !== null && self::$rbacManager !== null && self::$rbacManager->userHasPermission(self::$userId, $permission) === true;
    }

    public function getCommonParameters(): array
    {
        return [
            'administrator' => self::$administrator,
            'userCan' => self::userCan(...),
            'ssrRoutes' => self::$ssrRoutes,
            'translator' => $this->translator,
        ];
    }
}
