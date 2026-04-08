<?php

declare(strict_types=1);

/**
 * XeoLlm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Contents;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dboard\Handlers\Commons\AbstractXeoLlm;

/**
 * Content Xeo LLM menu AJAX action.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class XeoLlm extends AbstractXeoLlm
{
    protected function getModelClass(): string { return Content::class; }
    protected function getEntityName(): string { return 'content'; }
    protected function getLlmMenuFkColumn(): string { return 'contentId'; }
}
