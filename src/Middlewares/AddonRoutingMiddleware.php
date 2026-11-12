<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Middlewares;

use Blackcube\Dcore\Services\AddonService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Status;

/**
 * Gates the routes of an add-on on its activation: the routes of an inactive add-on answer 404.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class AddonRoutingMiddleware implements MiddlewareInterface
{
    private string $addonId = '';

    public function __construct(
        private readonly AddonService $addonService,
        private readonly ResponseFactoryInterface $responseFactory,
    ) {}

    /**
     * Middleware definition of the routes of an add-on, open while it is active.
     */
    public static function of(string $addonId): array
    {
        return [
            'class' => static::class,
            'withAddonId()' => [
                $addonId,
            ],
        ];
    }

    public function withAddonId(string $addonId): static
    {
        $new = clone $this;
        $new->addonId = $addonId;
        return $new;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $requestHandler
    ): ResponseInterface {
        $response = null;
        if ($this->addonService->isActive($this->addonId) === true) {
            $response = $requestHandler->handle($request);
        } else {
            $response = $this->responseFactory->createResponse(Status::NOT_FOUND);
        }
        return $response;
    }
}
