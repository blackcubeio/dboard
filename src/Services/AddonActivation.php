<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Dboard\Interfaces\Oauth2Interface;
use Blackcube\Dboard\Models\AuthorizationCode;
use Blackcube\Dboard\Models\OauthClient;
use Blackcube\Dcore\Services\AddonService;
use Throwable;
use Yiisoft\Db\Connection\ConnectionInterface;

/**
 * Activates, updates and deactivates an add-on, the whole of it: the migrations and the
 * registry through the dcore service, the roles and permissions through the RBAC
 * initializer, the OAuth2 clients of its population. The Addons screen and the console
 * commands call it, nothing else knows the order of the steps.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class AddonActivation
{
    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly AddonService $addonService,
        private readonly Modules $modules,
        private readonly RbacInitializer $rbacInitializer,
    ) {}

    /**
     * Plays the migrations of the add-on and writes its row, then puts its roles and
     * permissions in the RBAC storage.
     */
    public function activate(string $id): void
    {
        $this->addonService->activate($id);
        $this->rbacInitializer->initialize();
    }

    /**
     * Plays the migrations a newer package brought, then synchronizes the RBAC storage.
     */
    public function update(string $id): void
    {
        $this->addonService->update($id);
        $this->rbacInitializer->initialize();
    }

    /**
     * Reverts the migrations of the add-on and removes its row, refused before anything is
     * touched when one of them is not revertible; then removes the OAuth2 clients and codes
     * of its population when no other active add-on publishes it; then removes its roles,
     * permissions and assignments from the RBAC storage.
     */
    public function deactivate(string $id): void
    {
        $class = $this->addonService->getClass($id);
        $this->addonService->deactivate($id);
        if (is_subclass_of($class, Oauth2Interface::class) === true) {
            $population = $class::getPopulation();
            if (isset($this->modules->getPublishedScopes()[$population]) === false) {
                $this->removeClients($population);
            }
        }
        $this->rbacInitializer->initialize();
    }

    /**
     * Removes the row of an add-on whose package is gone, its tables untouched.
     */
    public function forget(string $id): void
    {
        $this->addonService->forget($id);
    }

    private function removeClients(string $population): void
    {
        $transaction = $this->db->beginTransaction();
        try {
            $clientQuery = OauthClient::query()->andWhere([
                'population' => $population,
            ]);
            /** @var OauthClient $client */
            foreach ($clientQuery->each() as $client) {
                $codeQuery = AuthorizationCode::query()->andWhere([
                    'clientId' => $client->getId(),
                ]);
                /** @var AuthorizationCode $code */
                foreach ($codeQuery->each() as $code) {
                    $code->delete();
                }
                $client->delete();
            }
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
