<?php

declare(strict_types=1);

/**
 * Token.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Authentication;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Handlers\Commons\AbstractBaseHandler;
use Blackcube\Dboard\Middlewares\JwtAuthMiddleware;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Services\Client;
use Blackcube\Dboard\Services\PasskeyGrant;
use Blackcube\Dboard\Services\RefreshToken;
use Blackcube\Oauth2\PopulationConfig;
use Blackcube\Oauth2\Server\Oauth2ServerFactory;
use Blackcube\Oauth2\Storage\Oauth2Storage;
use OAuth2\Request as Oauth2Request;
use OAuth2\Response as Oauth2Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cookies\Cookie;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * OAuth2 Token endpoint.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Token extends AbstractBaseHandler
{
    public function __construct(
        LoggerInterface $logger,
        DboardConfig $dboardConfig,
        WebViewRenderer $viewRenderer,
        ResponseFactoryInterface $responseFactory,
        JsonResponseFactory $jsonResponseFactory,
        UrlGeneratorInterface $urlGenerator,
        Aliases $aliases,
        TranslatorInterface $translator,
        protected StreamFactoryInterface $streamFactory,
    ) {
        parent::__construct(
            logger: $logger,
            dboardConfig: $dboardConfig,
            viewRenderer: $viewRenderer,
            responseFactory: $responseFactory,
            jsonResponseFactory: $jsonResponseFactory,
            urlGenerator: $urlGenerator,
            aliases: $aliases,
            translator: $translator,
        );
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $config = new PopulationConfig(
            name: 'dboard',
            issuer: $this->dboardConfig->cypherKey->getId(),
            audience: 'dboard',
            userQueryClass: Administrator::class,
            clientQueryClass: Client::class,
            refreshTokenQueryClass: RefreshToken::class,
            cypherKeyQueryClass: $this->dboardConfig->cypherKey::class,
            accessTokenTtl: 3600,
            refreshTokenTtl: 86400,
            allowedGrants: ['password', 'refresh_token', 'passkey'],
        );

        $storage = new Oauth2Storage(
            userClass: $config->userQueryClass,
            clientClass: $config->clientQueryClass,
            refreshTokenClass: $config->refreshTokenQueryClass,
            scopeProvider: $this->dboardConfig->scopeProvider,
            cypherKeyClass: $config->cypherKeyQueryClass,
        );

        // Create passkey grant
        $passkeyGrant = new PasskeyGrant($storage, $this->dboardConfig->webauthnConfig, $request);

        // Create server with custom grant
        $server = Oauth2ServerFactory::create($storage, $config, [$passkeyGrant]);

        $oauth2Request = $this->convertRequest($request);
        $oauth2Response = new Oauth2Response();

        $server->handleTokenRequest($oauth2Request, $oauth2Response);

        return $this->convertResponse($oauth2Response);
    }

    private function convertRequest(ServerRequestInterface $request): Oauth2Request
    {
        $parsedBody = $request->getParsedBody();
        $post = is_array($parsedBody) ? $parsedBody : [];

        return new Oauth2Request(
            query: $request->getQueryParams(),
            request: $post,
            attributes: [],
            cookies: $request->getCookieParams(),
            files: [],
            server: $request->getServerParams(),
            content: null,
            headers: $this->flattenHeaders($request->getHeaders())
        );
    }

    private function convertResponse(Oauth2Response $oauth2Response): ResponseInterface
    {
        $statusCode = $oauth2Response->getStatusCode();
        $response = $this->responseFactory->createResponse($statusCode);

        foreach ($oauth2Response->getHttpHeaders() as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        $body = $oauth2Response->getResponseBody();

        if ($statusCode === 200 && $body !== null && $body !== '') {
            $tokenData = json_decode($body, true);

            if (isset($tokenData['access_token'])) {
                $accessCookie = new Cookie(JwtAuthMiddleware::COOKIE_ACCESS_TOKEN, $tokenData['access_token']);
                $accessCookie = $accessCookie
                    ->withPath($this->dboardConfig->routePrefix)
                    ->withSameSite(Cookie::SAME_SITE_LAX)
                    ->withSecure(true)
                    ->withHttpOnly(false);
                $response = $accessCookie->addToResponse($response);

                if (isset($tokenData['refresh_token'])) {
                    $refreshCookie = new Cookie(JwtAuthMiddleware::COOKIE_REFRESH_TOKEN, $tokenData['refresh_token']);
                    $refreshCookie = $refreshCookie
                        ->withPath($this->dboardConfig->routePrefix)
                        ->withSameSite(Cookie::SAME_SITE_LAX)
                        ->withSecure(true)
                        ->withHttpOnly(true);
                    $response = $refreshCookie->addToResponse($response);
                }

                $redirectUrl = $this->urlGenerator->generate('dboard.dashboard');
                $responseBody = json_encode(['redirect' => $redirectUrl], JSON_THROW_ON_ERROR);
                $stream = $this->streamFactory->createStream($responseBody);
                $response = $response->withBody($stream);
                $response = $response->withHeader('Content-Type', 'application/json');

                return $response;
            }
        }

        if ($body !== null && $body !== '') {
            $stream = $this->streamFactory->createStream($body);
            $response = $response->withBody($stream);
        }

        return $response;
    }

    private function flattenHeaders(array $headers): array
    {
        $flattened = [];
        foreach ($headers as $name => $values) {
            $flattened[$name] = implode(', ', $values);
        }
        return $flattened;
    }
}
