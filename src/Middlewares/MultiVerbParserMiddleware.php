<?php

declare(strict_types=1);

/**
 * MultiVerbParserMiddleware.php
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
 * Middleware that parses request body for PUT, PATCH, DELETE methods.
 * PHP only auto-populates $_POST for POST requests, so this middleware
 * ensures getParsedBody() works for all methods with form data.
 */
final class MultiVerbParserMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = $request->getMethod();

        // Only process non-POST methods that might have a body
        if (!in_array($method, ['PUT', 'PATCH', 'DELETE'], true)) {
            return $handler->handle($request);
        }

        // Skip if body already parsed
        $parsedBody = $request->getParsedBody();
        if (!empty($parsedBody)) {
            return $handler->handle($request);
        }

        $contentType = $request->getHeaderLine('Content-Type');

        // Handle multipart/form-data
        if (str_contains($contentType, 'multipart/form-data')) {
            $parsedBody = $this->parseMultipartFormData($request);
            // Handle application/x-www-form-urlencoded
        } elseif (str_contains($contentType, 'application/x-www-form-urlencoded')) {
            $body = (string) $request->getBody();
            parse_str($body, $parsedBody);
            // Handle application/json
        } elseif (str_contains($contentType, 'application/json')) {
            $body = (string) $request->getBody();
            $parsedBody = json_decode($body, true) ?? [];
        }

        if (!empty($parsedBody)) {
            $request = $request->withParsedBody($parsedBody);
        }

        return $handler->handle($request);
    }

    /**
     * Parse multipart/form-data body manually.
     * This is needed because PHP doesn't populate $_POST for non-POST requests.
     */
    private function parseMultipartFormData(ServerRequestInterface $request): array
    {
        $contentType = $request->getHeaderLine('Content-Type');

        // Extract boundary
        if (!preg_match('/boundary=(.+)$/i', $contentType, $matches)) {
            return [];
        }

        $boundary = $matches[1];
        $body = (string) $request->getBody();

        // Split by boundary
        $parts = preg_split('/-+' . preg_quote($boundary, '/') . '/', $body);
        if ($parts === false) {
            return [];
        }

        $pairs = [];

        foreach ($parts as $part) {
            if (empty($part) || $part === "--\r\n" || $part === "--") {
                continue;
            }

            // Split headers and content
            $segments = preg_split('/\r\n\r\n/', $part, 2);
            if ($segments === false || count($segments) < 2) {
                continue;
            }

            [$headers, $content] = $segments;

            // Parse Content-Disposition header
            if (!preg_match('/Content-Disposition:.*name="([^"]+)"/i', $headers, $nameMatch)) {
                continue;
            }

            $name = $nameMatch[1];
            $value = rtrim($content, "\r\n");

            // Skip file uploads (they have filename in the header)
            if (preg_match('/filename="/i', $headers)) {
                continue;
            }

            // Accumulate as query string pairs - parse_str handles array notation
            $pairs[] = urlencode($name) . '=' . urlencode($value);
        }

        $data = [];
        if (!empty($pairs)) {
            parse_str(implode('&', $pairs), $data);
        }

        return $data;
    }
}
