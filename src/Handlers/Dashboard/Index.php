<?php

declare(strict_types=1);

/**
 * Index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Dashboard;

use Blackcube\Dboard\Handlers\Commons\AbstractBaseHandler;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Tag;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Dashboard index action.
 */
final class Index extends AbstractBaseHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $contentsActiveAvailable = Content::query()->active()->available()->count();
        $contentsActiveTotal = Content::query()->active()->count();

        $stats = [
            'contentsActive' => $contentsActiveAvailable,
            'contentsOutOfDate' => $contentsActiveTotal - $contentsActiveAvailable,
            'contentsInactive' => Content::query()->active(false)->count(),
            'tagsActive' => Tag::query()->active()->count(),
            'tagsInactive' => Tag::query()->active(false)->count(),
        ];

        $latestContentsQuery = Content::query()
            ->orderBy(['dateUpdate' => SORT_DESC])
            ->limit(5);

        $latestTagsQuery = Tag::query()
            ->orderBy(['dateUpdate' => SORT_DESC])
            ->limit(5);

        return $this->render('Dashboard/index', [
            'stats' => $stats,
            'latestContentsQuery' => $latestContentsQuery,
            'latestTagsQuery' => $latestTagsQuery,
            'urlGenerator' => $this->urlGenerator,
        ]);
    }
}
