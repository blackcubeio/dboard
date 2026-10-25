<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Addons;

/**
 * An addon whose resource is protected by the dboard authorization server.
 *
 * getPopulation() names the OAuth2 population of the resource: the issuer and
 * audience of its tokens, and the segment of its endpoints
 * (/dboard/oauth/{population}/authorize, token, register). getScopes() lists
 * the dboard permissions a client can be granted on that resource: the consent
 * screen offers them to the administrator, bounded by the permissions the
 * administrator holds.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
interface AddonOauth2Interface
{
    public static function getPopulation(): string;

    /**
     * @return string[]
     */
    public static function getScopes(): array;
}
