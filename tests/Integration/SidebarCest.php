<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Tests\Integration;

use Blackcube\Dboard\Components\Rbac;
use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Services\RbacInitializer;
use Blackcube\Dboard\Tests\Support\DatabaseCestTrait;
use Blackcube\Dboard\Tests\Support\IntegrationTester;
use Blackcube\Dboard\Widgets\Sidebar;
use Yiisoft\Rbac\Db\AssignmentsStorage;
use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\Manager;
use Yiisoft\Router\FastRoute\UrlGenerator;
use Yiisoft\Router\RouteCollection;
use Yiisoft\Router\RouteCollector;

/**
 * The bar of ink against the database: its entries follow the permissions
 * held by the administrator in the RBAC tables, the current one is marked,
 * and the foot carries the account.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class SidebarCest
{
    use DatabaseCestTrait;

    private const EMAIL = 'philippe@blackcube.io';

    public function testEntriesFollowThePermissions(IntegrationTester $I): void
    {
        $I->wantTo('verify the sidebar shows the entries the administrator is allowed to, and no other');

        $administrator = $this->createAdministrator();
        $dboardConfig = new DboardConfig(rbacClasses: [
            Rbac::class,
        ]);
        $itemsStorage = new ItemsStorage($this->db);
        $manager = new Manager($itemsStorage, new AssignmentsStorage($this->db), enableDirectPermissions: true);
        $rbacInitializer = new RbacInitializer($manager, $itemsStorage, $dboardConfig);
        $rbacInitializer->initialize();
        $manager->assign(Rbac::PERMISSION_CONTENT_VIEW, $administrator->getId());

        $sidebar = new Sidebar($manager, $this->createUrlGenerator(), $dboardConfig);
        $html = $sidebar
            ->user($administrator)
            ->currentRoute('dboard.contents')
            ->render();

        $I->assertStringContainsString('href="/dboard/contents/"', $html);
        $I->assertStringContainsString('aria-current="page"', $html);
        $I->assertSame(1, substr_count($html, 'href="/dboard/dashboard"'), 'the brand link only: no dashboard entry without its permission');
        $I->assertStringNotContainsString('/dboard/administrators', $html);
        $I->assertStringNotContainsString('/dboard/tags', $html);
    }

    public function testFootCarriesTheAccount(IntegrationTester $I): void
    {
        $I->wantTo('verify the foot of the sidebar shows the administrator, the account link and the logout link');

        $administrator = $this->createAdministrator();
        $dboardConfig = new DboardConfig(rbacClasses: [
            Rbac::class,
        ]);
        $itemsStorage = new ItemsStorage($this->db);
        $manager = new Manager($itemsStorage, new AssignmentsStorage($this->db), enableDirectPermissions: true);

        $sidebar = new Sidebar($manager, $this->createUrlGenerator(), $dboardConfig);
        $html = $sidebar
            ->user($administrator)
            ->currentRoute('dboard.index')
            ->render();

        $I->assertStringContainsString('Philippe Gaultier', $html);
        $I->assertStringContainsString(self::EMAIL, $html);
        $I->assertStringContainsString('href="/dboard/account/' . $administrator->getId() . '"', $html);
        $I->assertStringContainsString('href="/dboard/logout"', $html);
        $I->assertStringNotContainsString('aria-current="page"', $html);
    }

    private function createAdministrator(): Administrator
    {
        $administrator = new Administrator();
        $administrator->setFirstname('Philippe');
        $administrator->setLastname('Gaultier');
        $administrator->setEmail(self::EMAIL);
        $administrator->setPassword('p');
        $administrator->setActive(true);
        $administrator->save();

        return $administrator;
    }

    /**
     * The routes of the package, as config/routes.php declares them with the
     * parameters of the package: the URLs of the bar are the real ones.
     */
    private function createUrlGenerator(): UrlGenerator
    {
        $params = require dirname(__DIR__, 2) . '/config/common/params.php';
        $routes = require dirname(__DIR__, 2) . '/config/routes.php';
        $routeCollector = new RouteCollector();
        $routeCollector->addRoute(...$routes);

        return new UrlGenerator(new RouteCollection($routeCollector));
    }
}
