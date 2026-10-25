<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeFormModel\Attributes\Bridge;
use Blackcube\Dboard\Components\Rbac;
use Yiisoft\Validator\Rule\Each;
use Yiisoft\Validator\Rule\In;

/**
 * Permissions form of the administrator drawer: the roles and the permissions
 * to assign, bounded by the RBAC items the code declares. An unchecked toggle
 * sends nothing, so loading starts from empty lists.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class PermissionForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-modules';

    protected array $roles = [];
    protected array $permissions = [];

    public function load(mixed $submittedValues, ?string $scope = null): bool
    {
        $this->roles = [];
        $this->permissions = [];
        return parent::load($submittedValues, $scope);
    }

    /**
     * @param string[] $roles
     */
    #[Bridge]
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return string[]
     */
    #[Bridge]
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param string[] $permissions
     */
    #[Bridge]
    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }

    /**
     * @return string[]
     */
    #[Bridge]
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function scenarios(): array
    {
        return [
            'default' => [
                'roles',
                'permissions',
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'roles' => [
                new Each([
                    new In(Rbac::getAllRoles()),
                ]),
            ],
            'permissions' => [
                new Each([
                    new In(Rbac::getAllPermissions()),
                ]),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'roles' => 'Roles',
            'permissions' => 'Permissions',
        ];
    }
}
