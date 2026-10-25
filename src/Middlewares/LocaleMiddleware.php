<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Middlewares;

use Blackcube\Dboard\Helpers\LocaleHelper;
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
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class LocaleMiddleware implements MiddlewareInterface
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $requestHandler
    ): ResponseInterface {
        $locale = $this->detectBrowserLocale($request);
        if ($locale !== null) {
            $this->translator->setLocale($locale);
        }

        return $requestHandler->handle($request);
    }

    private function detectBrowserLocale(ServerRequestInterface $request): ?string
    {
        $locale = null;
        $acceptLanguage = $request->getHeaderLine('Accept-Language');
        if ($acceptLanguage !== '') {
            $browserLocale = Locale::acceptFromHttp($acceptLanguage);
            if ($browserLocale !== false) {
                $primary = Locale::getPrimaryLanguage($browserLocale);
                $supported = LocaleHelper::getLocales();
                if (in_array($primary, $supported, true) === true) {
                    $locale = $primary;
                }
            }
        }

        return $locale;
    }
}
