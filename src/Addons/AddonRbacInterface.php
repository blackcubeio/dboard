<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Addons;

/**
 * An addon that contributes RBAC items to the dboard admin.
 *
 * getRbac() returns the class-string of a class holding PERMISSION_* / ROLE_*
 * constants in the dboard convention ('DOMAIN:ACTION'); dboard appends it to
 * its rbacClasses, which RbacInitializer reads by reflection.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
interface AddonRbacInterface
{
    /**
     * @return class-string
     */
    public static function getRbac(): string;
}
