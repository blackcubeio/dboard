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
use Blackcube\Dboard\Services\Modules;
use Blackcube\Dboard\Services\RbacInitializer;
use Blackcube\Dboard\Tests\Support\DatabaseCestTrait;
use Blackcube\Dboard\Tests\Support\IntegrationTester;
use Blackcube\Dboard\Tests\Support\Modules\ProbeModule;
use Blackcube\Dboard\Tests\Support\Modules\ProbeModuleRbac;
use Blackcube\Dboard\Widgets\Sidebar;
use Blackcube\Dcore\Services\AddonService;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Injector\Injector;
use Yiisoft\Rbac\Db\AssignmentsStorage;
use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\Manager;
use Yiisoft\Router\FastRoute\UrlGenerator;
use Yiisoft\Router\RouteCollection;
use Yiisoft\Router\RouteCollectionInterface;
use Yiisoft\Router\RouteCollector;
use Yiisoft\Router\RouteNotFoundException;

/**
 * A module against the database: declared in 'blackcube/dboard' => 'modules', it brings its
 * RBAC items, its sidebar entry, its routes and its OAuth2 population with no activation; not
 * declared, it brings nothing.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ModulesCest
{
    use DatabaseCestTrait;

    public function testADeclaredModuleContributesWithoutActivation(IntegrationTester $I): void
    {
        $I->wantTo('verify a declared module brings its RBAC items, its sidebar entry, its route and its population with no activation');

        $modules = $this->createModules([
            ProbeModule::class,
        ]);
        $itemsStorage = new ItemsStorage($this->db);
        $manager = new Manager($itemsStorage, new AssignmentsStorage($this->db), enableDirectPermissions: true);
        $rbacInitializer = new RbacInitializer($manager, $itemsStorage, $modules);
        $rbacInitializer->initialize();
        $administrator = $this->createAdministrator();
        $manager->assign(ProbeModuleRbac::PERMISSION_PROBE_MODULE_VIEW, $administrator->getId());
        $routeCollection = $this->createRouteCollection([
            ProbeModule::class,
        ]);

        $I->assertSame([
            ProbeModule::class,
        ], $modules->getClasses());
        $I->assertContains(ProbeModuleRbac::class, $modules->getRbacClasses());
        $I->assertNotNull($manager->getPermission(ProbeModuleRbac::PERMISSION_PROBE_MODULE_VIEW), 'its permission');
        $I->assertNotNull($manager->getRole(ProbeModuleRbac::ROLE_PROBE_MODULE_MANAGER), 'its role');
        $I->assertTrue($manager->hasChild(Rbac::ROLE_ADMIN, ProbeModuleRbac::ROLE_PROBE_MODULE_MANAGER), 'ADMIN holds the role of the module');
        $I->assertSame([
            ProbeModule::POPULATION => [
                ProbeModuleRbac::PERMISSION_PROBE_MODULE_VIEW,
            ],
        ], $modules->getPublishedScopes());
        $I->assertSame(ProbeModule::URL, (new UrlGenerator($routeCollection))->generate(ProbeModule::ROUTE), 'its route, mounted under /dboard');
        $I->assertStringContainsString('href="' . ProbeModule::URL . '"', $this->renderSidebar($manager, $modules, $routeCollection, $administrator), 'its sidebar entry');
    }

    public function testAModuleNotDeclaredContributesNothing(IntegrationTester $I): void
    {
        $I->wantTo('verify a module that is not declared brings no RBAC item, no scope, no route and no sidebar entry');

        $modules = $this->createModules([]);
        $itemsStorage = new ItemsStorage($this->db);
        $manager = new Manager($itemsStorage, new AssignmentsStorage($this->db), enableDirectPermissions: true);
        $rbacInitializer = new RbacInitializer($manager, $itemsStorage, $modules);
        $rbacInitializer->initialize();
        $administrator = $this->createAdministrator();
        $routeCollection = $this->createRouteCollection([]);

        $I->assertSame([], $modules->getClasses());
        $I->assertSame([
            Rbac::class,
        ], $modules->getRbacClasses());
        $I->assertNull($manager->getPermission(ProbeModuleRbac::PERMISSION_PROBE_MODULE_VIEW));
        $I->assertSame([], $modules->getPublishedScopes());
        $I->expectThrowable(RouteNotFoundException::class, function () use ($routeCollection): void {
            $routeCollection->getRoute(ProbeModule::ROUTE);
        });
        $I->assertStringNotContainsString(ProbeModule::URL, $this->renderSidebar($manager, $modules, $routeCollection, $administrator));
    }

    /**
     * The modules of the panel with no add-on declared: the panel's RBAC class and the given
     * module classes.
     *
     * @param list<class-string> $modules
     */
    private function createModules(array $modules): Modules
    {
        $containerConfig = ContainerConfig::create()
            ->withDefinitions([
                ConnectionInterface::class => $this->db,
            ]);
        $injector = new Injector(new Container($containerConfig));

        return new Modules(new AddonService($this->db, $injector, []), [
            Rbac::class,
        ], $modules);
    }

    /**
     * The routes of the package, as config/routes.php declares them with the parameters of the
     * package and the given modules declared.
     *
     * @param list<class-string> $modules
     */
    private function createRouteCollection(array $modules): RouteCollectionInterface
    {
        $params = require dirname(__DIR__, 2) . '/config/common/params.php';
        $params['blackcube/dboard']['modules'] = $modules;
        $routes = require dirname(__DIR__, 2) . '/config/routes.php';
        $routeCollector = new RouteCollector();
        $routeCollector->addRoute(...$routes);

        return new RouteCollection($routeCollector);
    }

    private function renderSidebar(Manager $manager, Modules $modules, RouteCollectionInterface $routeCollection, Administrator $administrator): string
    {
        $dboardConfig = new DboardConfig(rbacClasses: [
            Rbac::class,
        ]);
        $sidebar = new Sidebar($manager, new UrlGenerator($routeCollection), $dboardConfig, $modules);

        return $sidebar
            ->user($administrator)
            ->currentRoute('dboard.index')
            ->render();
    }

    private function createAdministrator(): Administrator
    {
        $administrator = new Administrator();
        $administrator->setFirstname('Philippe');
        $administrator->setLastname('Gaultier');
        $administrator->setEmail('philippe@blackcube.io');
        $administrator->setPassword('p');
        $administrator->setActive(true);
        $administrator->save();

        return $administrator;
    }
}
