<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class MultiVerbParserMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $requestHandler): ResponseInterface
    {
        $method = $request->getMethod();

        if (in_array($method, [
            'PUT',
            'PATCH',
            'DELETE',
        ], true) === true) {
            $parsedBody = $request->getParsedBody();
            if (empty($parsedBody) === true) {
                $contentType = $request->getHeaderLine('Content-Type');

                if (str_contains($contentType, 'multipart/form-data') === true) {
                    $parsedBody = $this->parseMultipartFormData($request);
                } elseif (str_contains($contentType, 'application/x-www-form-urlencoded') === true) {
                    $body = (string) $request->getBody();
                    parse_str($body, $parsedBody);
                } elseif (str_contains($contentType, 'application/json') === true) {
                    $body = (string) $request->getBody();
                    $parsedBody = json_decode($body, true) ?? [];
                }

                if (empty($parsedBody) === false) {
                    $request = $request->withParsedBody($parsedBody);
                }
            }
        }

        return $requestHandler->handle($request);
    }

    /**
     * Parse multipart/form-data body manually.
     * This is needed because PHP doesn't populate $_POST for non-POST requests.
     */
    private function parseMultipartFormData(ServerRequestInterface $request): array
    {
        $parsedBody = [];
        $contentType = $request->getHeaderLine('Content-Type');

        if (preg_match('/boundary=(.+)$/i', $contentType, $matches) === 1) {
            $boundary = $matches[1];
            $body = (string) $request->getBody();

            $parts = preg_split('/-+' . preg_quote($boundary, '/') . '/', $body);
            if ($parts !== false) {
                $pairs = [];

                foreach ($parts as $part) {
                    if (empty($part) === false && $part !== "--\r\n" && $part !== '--') {
                        $segments = preg_split('/\r\n\r\n/', $part, 2);
                        if ($segments !== false && count($segments) >= 2) {
                            [$headers, $content] = $segments;

                            if (preg_match('/Content-Disposition:.*name="([^"]+)"/i', $headers, $nameMatch) === 1 && preg_match('/filename="/i', $headers) !== 1) {
                                $name = $nameMatch[1];
                                $fieldValue = rtrim($content, "\r\n");
                                $pairs[] = urlencode($name) . '=' . urlencode($fieldValue);
                            }
                        }
                    }
                }

                if (empty($pairs) === false) {
                    parse_str(implode('&', $pairs), $parsedBody);
                }
            }
        }

        return $parsedBody;
    }
}
