<?php

declare(strict_types=1);

/**
 * JsonBodyParserMiddleware.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware that parses JSON request body into getParsedBody().
 * PHP only auto-populates $_POST for application/x-www-form-urlencoded
 * and multipart/form-data. This middleware handles application/json.
 */
final readonly class JsonBodyParserMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $contentType = $request->getHeaderLine('Content-Type');

        if (str_contains($contentType, 'application/json')) {
            $body = (string) $request->getBody();
            $parsedBody = json_decode($body, true);
            if (is_array($parsedBody)) {
                $request = $request->withParsedBody($parsedBody);
            }
        }

        return $handler->handle($request);
    }
}
