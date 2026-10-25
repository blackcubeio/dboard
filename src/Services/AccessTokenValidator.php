<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Blackcube\Dboard\Models\Administrator;
use Blackcube\Oauth2\Jwt\CypherKey;
use Blackcube\Oauth2\Jwt\JwtService;
use Yiisoft\Rbac\ManagerInterface;

/**
 * Validates, for the resource of an addon population, an access token issued
 * by the dboard authorization server: signature with the dboard key, issuer and
 * audience equal to the population, expiration, active administrator. The
 * permissions handed back are the granted scopes bounded by the permissions
 * the administrator holds right now: a right lost in dboard is lost on the
 * resource at once.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class AccessTokenValidator
{
    public function __construct(
        private readonly CypherKey $cypherKey,
        private readonly JwtService $jwtService,
        private readonly ManagerInterface $rbacManager,
        private readonly AddonPopulations $populations,
    ) {
    }

    /**
     * @return array{administrator: Administrator, permissions: string[]}|null
     */
    public function validate(string $token, string $population): ?array
    {
        $tokenGrant = null;
        $claims = null;
        if ($this->populations->get($population) !== null) {
            $claims = $this->jwtService->decode($token, $this->cypherKey);
        }
        if ($claims !== null && $claims->iss === $population && $claims->aud === $population && $claims->isExpired() === false) {
            $administrator = Administrator::query()
                ->andWhere([
                    'id' => (int) $claims->sub,
                ])
                ->active()
                ->one();
            if ($administrator !== null) {
                $userPermissions = $this->rbacManager->getPermissionsByUserId($claims->sub);
                $held = [];
                foreach ($userPermissions as $permissionName => $permission) {
                    $held[$permissionName] = $permission->getName();
                }
                $tokenGrant = [
                    'administrator' => $administrator,
                    'permissions' => array_values(array_intersect($claims->scopes, $held)),
                ];
            }
        }
        return $tokenGrant;
    }
}
