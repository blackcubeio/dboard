<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Logout extends AbstractBaseHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $logoutResponse = $this->redirect('dboard.login');

        $expiredDate = new \DateTimeImmutable('-1 year');
        $accessCookie = (new Cookie(JwtAuthMiddleware::COOKIE_ACCESS_TOKEN, ''))
            ->withPath($this->dboardConfig->routePrefix)
            ->withExpires($expiredDate);
        $refreshCookie = (new Cookie(JwtAuthMiddleware::COOKIE_REFRESH_TOKEN, ''))
            ->withPath($this->dboardConfig->routePrefix)
            ->withExpires($expiredDate);

        $logoutResponse = $accessCookie->addToResponse($logoutResponse);
        $logoutResponse = $refreshCookie->addToResponse($logoutResponse);

        return $logoutResponse;
    }
}
