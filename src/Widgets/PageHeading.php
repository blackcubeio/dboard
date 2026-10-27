<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Widgets;

use Yiisoft\Widget\Widget;

/**
 * The heading of a page: a sober title, an optional subtitle, and the actions
 * of the page on the right. The markup lives in Views/page-heading.php.
 *
 * Usage:
 *   Widgets::pageHeading($title)
 *       ->subtitle($subtitle)
 *       ->addAction($renderedButton)
 *       ->render()
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class PageHeading extends Widget
{
    private string $title = '';
    private ?string $subtitle = null;
    /** @var string[] rendered actions */
    private array $actions = [];

    public function title(string $title): self
    {
        $new = clone $this;
        $new->title = $title;
        return $new;
    }

    public function subtitle(?string $subtitle): self
    {
        $new = clone $this;
        $new->subtitle = $subtitle;
        return $new;
    }

    /**
     * Adds a rendered action (a button or a link) to the right of the title.
     */
    public function addAction(string $action): self
    {
        $new = clone $this;
        $new->actions[] = $action;
        return $new;
    }

    public function render(): string
    {
        $params = [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'actions' => $this->actions,
        ];

        extract($params, EXTR_OVERWRITE);
        ob_start();
        include __DIR__ . '/Views/page-heading.php';
        return ob_get_clean();
    }
}
