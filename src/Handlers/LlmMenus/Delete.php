<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\LlmMenus;

use Blackcube\Dcore\Models\LlmMenu;
use Blackcube\Dboard\Handlers\Commons\AbstractDelete;

/**
 * LlmMenu delete action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Delete extends AbstractDelete
{
    protected function getModelClass(): string { return LlmMenu::class; }
    protected function getEntityName(): string { return 'llmMenu'; }
    protected function getListId(): string { return 'llmmenus-list'; }
    protected function getListRoute(): string { return 'dboard.llmmenus'; }
    protected function getSuccessMessage(): string { return $this->translator->translate('LLM menu "{name}" has been deleted.', [
        'name' => $this->getModelName(),
    ], 'dboard-content'); }
}
