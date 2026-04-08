<?php

declare(strict_types=1);

/**
 * Logout.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Authentication;

use Blackcube\Dboard\Handlers\Commons\AbstractBaseHandler;
use Blackcube\Dboard\Middlewares\JwtAuthMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Cookies\Cookie;

/**
 * Logout action.
 */
final class Logout extends AbstractBaseHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $response = $this->redirect('dboard.login');

        $expiredDate = new \DateTimeImmutable('-1 year');
        $accessCookie = (new Cookie(JwtAuthMiddleware::COOKIE_ACCESS_TOKEN, ''))
            ->withPath($this->dboardConfig->routePrefix)
            ->withExpires($expiredDate);
        $refreshCookie = (new Cookie(JwtAuthMiddleware::COOKIE_REFRESH_TOKEN, ''))
            ->withPath($this->dboardConfig->routePrefix)
            ->withExpires($expiredDate);

        $response = $accessCookie->addToResponse($response);
        $response = $refreshCookie->addToResponse($response);

        return $response;
    }
}
