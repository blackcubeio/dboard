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
use Blackcube\Dboard\Middlewares\AddonRoutingMiddleware;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\AuthorizationCode;
use Blackcube\Dboard\Models\OauthClient;
use Blackcube\Dboard\Services\AddonActivation;
use Blackcube\Dboard\Services\Modules;
use Blackcube\Dboard\Services\RbacInitializer;
use Blackcube\Dboard\Tests\Support\Addons\ProbeAddon;
use Blackcube\Dboard\Tests\Support\Addons\ProbeRbac;
use Blackcube\Dboard\Tests\Support\DatabaseCestTrait;
use Blackcube\Dboard\Tests\Support\IntegrationTester;
use Blackcube\Dboard\Widgets\Sidebar;
use Blackcube\Dcore\Services\AddonService;
use DateTimeImmutable;
use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Injector\Injector;
use Yiisoft\Rbac\Db\AssignmentsStorage;
use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\Manager;
use Yiisoft\Router\FastRoute\UrlGenerator;
use Yiisoft\Router\RouteCollection;
use Yiisoft\Router\RouteCollector;

/**
 * The activation and deactivation of an add-on against the database, the whole of it: its
 * table, its roles and permissions, its sidebar entry, its OAuth2 population and clients,
 * and the gate on its routes.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class AddonsCest
{
    use DatabaseCestTrait;

    private const ADDON_ID = 'probe';

    private AddonService $addonService;
    private Modules $modules;
    private Manager $manager;
    private AddonActivation $addonActivation;
    private DboardConfig $dboardConfig;

    public function testActivationBringsEverything(IntegrationTester $I): void
    {
        $I->wantTo('verify an activated add-on has its table, its permissions, its sidebar entry and its population');

        $this->setUpServices();
        $administrator = $this->createAdministrator();

        $this->addonActivation->activate(self::ADDON_ID);

        $I->assertTrue($this->addonService->isActive(self::ADDON_ID));
        $I->assertNotNull($this->db->getTableSchema('addonProbes', true), 'the table of the add-on');
        $I->assertNotNull($this->manager->getPermission(ProbeRbac::PERMISSION_PROBE_VIEW), 'its permission');
        $I->assertNotNull($this->manager->getRole(ProbeRbac::ROLE_PROBE_MANAGER), 'its role');
        $I->assertTrue($this->manager->hasChild(ProbeRbac::ROLE_PROBE_MANAGER, ProbeRbac::PERMISSION_PROBE_VIEW));
        $I->assertTrue($this->manager->hasChild(Rbac::ROLE_ADMIN, ProbeRbac::ROLE_PROBE_MANAGER), 'ADMIN holds the role of the add-on');
        $I->assertSame([
            ProbeAddon::POPULATION => [
                ProbeRbac::PERMISSION_PROBE_VIEW,
            ],
        ], $this->modules->getPublishedScopes());

        $this->manager->assign(ProbeRbac::PERMISSION_PROBE_VIEW, $administrator->getId());
        $I->assertStringContainsString('href="' . ProbeAddon::SIDEBAR_URL . '"', $this->renderSidebar($administrator), 'the sidebar entry of the add-on');
    }

    public function testDeactivationRemovesEverything(IntegrationTester $I): void
    {
        $I->wantTo('verify a deactivated add-on leaves no table, no permission, no assignment, no OAuth2 client, no sidebar entry');

        $this->setUpServices();
        $administrator = $this->createAdministrator();
        $this->addonActivation->activate(self::ADDON_ID);
        $this->manager->assign(ProbeRbac::PERMISSION_PROBE_VIEW, $administrator->getId());
        $this->createClient();
        $this->createCode($administrator);

        $this->addonActivation->deactivate(self::ADDON_ID);

        $I->assertFalse($this->addonService->isActive(self::ADDON_ID));
        $I->assertNull($this->db->getTableSchema('addonProbes', true), 'the table is gone');
        $I->assertNull($this->manager->getPermission(ProbeRbac::PERMISSION_PROBE_VIEW), 'the permission is gone');
        $I->assertNull($this->manager->getRole(ProbeRbac::ROLE_PROBE_MANAGER), 'the role is gone');
        $I->assertFalse($this->manager->userHasPermission($administrator->getId(), ProbeRbac::PERMISSION_PROBE_VIEW), 'the assignment is gone');
        $I->assertNotNull($this->manager->getPermission(Rbac::PERMISSION_CONTENT_VIEW), 'the permissions of the panel stay');
        $I->assertNull(OauthClient::queryById('probe-client'), 'the client of the population is gone');
        $I->assertNull(AuthorizationCode::queryByCode('probe-code'), 'its authorization code with it');
        $I->assertSame([], $this->modules->getPublishedScopes());
        $I->assertStringNotContainsString(ProbeAddon::SIDEBAR_URL, $this->renderSidebar($administrator));
    }

    public function testTheGateAnswers404WhileInactive(IntegrationTester $I): void
    {
        $I->wantTo('verify the routes of an add-on answer 404 while it is inactive and pass once it is active');

        $this->setUpServices();
        $middleware = (new AddonRoutingMiddleware($this->addonService, new ResponseFactory()))
            ->withAddonId(self::ADDON_ID);
        $request = (new ServerRequestFactory())->createServerRequest('GET', ProbeAddon::SIDEBAR_URL);
        $handler = new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return (new ResponseFactory())->createResponse(200);
            }
        };

        $I->assertSame(404, $middleware->process($request, $handler)->getStatusCode());

        $this->addonActivation->activate(self::ADDON_ID);

        $I->assertSame(200, $middleware->process($request, $handler)->getStatusCode());
    }

    private function setUpServices(): void
    {
        $containerConfig = ContainerConfig::create()
            ->withDefinitions([
                ConnectionInterface::class => $this->db,
            ]);
        $injector = new Injector(new Container($containerConfig));
        $this->dboardConfig = new DboardConfig(rbacClasses: [
            Rbac::class,
        ]);
        $this->addonService = new AddonService($this->db, $injector, [
            self::ADDON_ID => ProbeAddon::class,
        ]);
        $this->modules = new Modules($this->addonService, [
            Rbac::class,
        ]);
        $itemsStorage = new ItemsStorage($this->db);
        $this->manager = new Manager($itemsStorage, new AssignmentsStorage($this->db), enableDirectPermissions: true);
        $rbacInitializer = new RbacInitializer($this->manager, $itemsStorage, $this->modules);
        $rbacInitializer->initialize();
        $this->addonActivation = new AddonActivation($this->db, $this->addonService, $this->modules, $rbacInitializer);
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

    /**
     * A client of the population of the add-on.
     */
    private function createClient(): void
    {
        $client = new OauthClient();
        $client->setId('probe-client');
        $client->setPopulation(ProbeAddon::POPULATION);
        $client->setName('Probe client');
        $client->setRedirectUris([
            'https://probe.test/callback',
        ]);
        $client->setAllowedGrants([
            'authorization_code',
        ]);
        $client->save();
    }

    /**
     * An authorization code of the administrator on that client.
     */
    private function createCode(Administrator $administrator): void
    {
        $code = new AuthorizationCode();
        $code->setCode('probe-code');
        $code->setUserId((string) $administrator->getId());
        $code->setClientId('probe-client');
        $code->setRedirectUri('https://probe.test/callback');
        $code->setExpires(new DateTimeImmutable('+1 minute'));
        $code->save();
    }

    private function renderSidebar(Administrator $administrator): string
    {
        $params = require dirname(__DIR__, 2) . '/config/common/params.php';
        $routes = require dirname(__DIR__, 2) . '/config/routes.php';
        $routeCollector = new RouteCollector();
        $routeCollector->addRoute(...$routes);
        $urlGenerator = new UrlGenerator(new RouteCollection($routeCollector));

        $sidebar = new Sidebar($this->manager, $urlGenerator, $this->dboardConfig, $this->modules);
        return $sidebar
            ->user($administrator)
            ->currentRoute('dboard.index')
            ->render();
    }
}
