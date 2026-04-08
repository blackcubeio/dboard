<?php

declare(strict_types=1);

/**
 * Popover.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Widgets;

use Yiisoft\Html\Html;
use Yiisoft\Widget\Widget;

/**
 * Popover tooltip widget for button bars.
 *
 * Usage:
 *   Widgets::popover('Slug / Sitemap')->id('content-popover-slug-sitemap')->render()
 */
final class Popover extends Widget
{
    private string $id = '';

    public function __construct(
        private readonly string $content = '',
    ) {
    }

    public function id(string $id): self
    {
        $new = clone $this;
        $new->id = $id;
        return $new;
    }

    public function render(): string
    {
        return Html::tag(
            'div',
            $this->content,
            [
                'bleet-popover' => $this->id,
                'class' => 'fixed z-50 hidden [&.is-open]:block bg-secondary-50 text-secondary-700 text-sm font-medium border border-secondary-900 rounded shadow-sm px-3 py-1.5 pointer-events-none whitespace-nowrap',
            ]
        )->render();
    }

}
