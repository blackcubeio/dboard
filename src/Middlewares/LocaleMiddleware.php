<?php

declare(strict_types=1);

/**
 * LocaleMiddleware.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Middlewares;

use Blackcube\Dboard\Services\LocaleHelper;
use Locale;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * Sets the translator locale from the browser Accept-Language header.
 * Must be placed BEFORE JwtAuthMiddleware in the middleware stack.
 * JwtAuthMiddleware overrides the locale if the administrator has one set.
 */
final class LocaleMiddleware implements MiddlewareInterface
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $locale = $this->resolveBrowserLocale($request);
        if ($locale !== null) {
            $this->translator->setLocale($locale);
        }

        return $handler->handle($request);
    }

    private function resolveBrowserLocale(ServerRequestInterface $request): ?string
    {
        $acceptLanguage = $request->getHeaderLine('Accept-Language');
        if ($acceptLanguage === '') {
            return null;
        }

        $browserLocale = Locale::acceptFromHttp($acceptLanguage);
        if ($browserLocale === false) {
            return null;
        }

        $primary = Locale::getPrimaryLanguage($browserLocale);
        $supported = LocaleHelper::getLocales();
        if (in_array($primary, $supported, true)) {
            return $primary;
        }

        return null;
    }
}
