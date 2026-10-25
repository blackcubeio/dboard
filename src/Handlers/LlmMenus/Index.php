<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\LlmMenus;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\LlmMenu;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Handlers\Commons\AbstractIndex;

/**
 * LlmMenus index action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Index extends AbstractIndex
{
    protected function getModelClass(): string { return LlmMenu::class; }
    protected function getViewPrefix(): string { return 'LlmMenus'; }

    /**
     * Eager-loads content and tag for the current page, removing the per-row N+1.
     *
     * @return array<int, LlmMenu> The current page entries, relations preloaded
     */
    protected function eagerLoad(): array
    {
        $items = [];
        $contentIds = [];
        $tagIds = [];
        foreach ($this->paginator->read() as $llmMenu) {
            /** @var LlmMenu $llmMenu */
            $items[] = $llmMenu;
            if ($llmMenu->getContentId() !== null) {
                $contentIds[$llmMenu->getContentId()] = $llmMenu->getContentId();
            }
            if ($llmMenu->getTagId() !== null) {
                $tagIds[$llmMenu->getTagId()] = $llmMenu->getTagId();
            }
        }

        $contents = $this->getIndexedRecords(Content::class, $contentIds);
        $tags = $this->getIndexedRecords(Tag::class, $tagIds);

        foreach ($items as $llmMenu) {
            $contentId = $llmMenu->getContentId();
            $llmMenu->populateRelation('content', $contentId !== null ? ($contents[$contentId] ?? null) : null);
            $tagId = $llmMenu->getTagId();
            $llmMenu->populateRelation('tag', $tagId !== null ? ($tags[$tagId] ?? null) : null);
        }

        return $items;
    }
}
