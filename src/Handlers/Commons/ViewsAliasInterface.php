<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

/**
 * A handler that declares the Yii alias of the views root it renders from
 * (e.g. '@dboard/Views'). An addon handler overrides it to render its own views
 * while keeping the dboard layout.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
interface ViewsAliasInterface
{
    public function getViewsAlias(): string;
}
