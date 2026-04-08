<?php

declare(strict_types=1);

/**
 * JwtAuthMiddleware.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Middlewares;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Services\Client;
use Blackcube\Dboard\Services\CypherKey;
use Blackcube\Dboard\Services\RefreshToken;
use Blackcube\Dboard\Services\ScopeProvider;
use Blackcube\Dboard\Services\ViewInjection;
use Blackcube\Oauth2\Middlewares\AbstractJwtMiddleware;
use Blackcube\Oauth2\PopulationConfig;
use Blackcube\Oauth2\Server\Oauth2ServerFactory;
use Blackcube\Oauth2\Storage\Oauth2Storage;
use Closure;
use OAuth2\Request as Oauth2Request;
use OAuth2\Response as Oauth2Response;
use Psr\Clock\ClockInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Yiisoft\Cookies\Cookie;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

final class JwtAuthMiddleware extends AbstractJwtMiddleware
{
    public const COOKIE_ACCESS_TOKEN = 'access_token';
    public const COOKIE_REFRESH_TOKEN = 'refresh_token';

    private string $loginRoute = 'dboard.login';

    /** @var string[] */
    private array $requiredPermissions = [];

    private bool $optional = false;

    public function __construct(
        private readonly CypherKey $cypherKey,
        private readonly ScopeProvider $scopeProvider,
        ClockInterface $clock,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly DboardConfig $dboardConfig,
        private readonly LoggerInterface $logger,
        private readonly ManagerInterface $rbacManager,
        private readonly TranslatorInterface $translator,
    ) {
        parent::__construct($clock);
    }

    public static function optional(): Closure
    {
        return static function (JwtAuthMiddleware $middleware): JwtAuthMiddleware {
            $new = clone $middleware;
            $new->optional = true;
            return $new;
        };
    }

    /**
     * @param string|string[] $permissions
     */
    public static function withRbac(string|array $permissions): Closure
    {
        return static function (JwtAuthMiddleware $middleware) use ($permissions): JwtAuthMiddleware {
            $new = clone $middleware;
            $new->requiredPermissions = is_array($permissions) ? $permissions : [$permissions];
            return $new;
        };
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $tokens = $this->extractTokens($request);
        $accessToken = $tokens[self::COOKIE_ACCESS_TOKEN];
        $refreshToken = $tokens[self::COOKIE_REFRESH_TOKEN];

        if ($accessToken === null && $refreshToken === null) {
            if ($this->optional) {
                return $handler->handle($request);
            }
            return $this->redirectTo($this->loginRoute);
        }
        $freshAccessToken = null;

        $claims = $this->validateAndParseToken($accessToken);

        $freshRefreshToken = null;
        if ($claims === null && $refreshToken !== null) {
            $refreshResult = $this->refreshAccessToken($refreshToken);
            if ($refreshResult !== null) {
                $freshAccessToken = $refreshResult['access_token'];
                $freshRefreshToken = $refreshResult['refresh_token'] ?? null;
                $claims = $this->validateAndParseToken($freshAccessToken);
            }
        }

        if ($claims === null) {
            if ($this->optional) {
                return $handler->handle($request);
            }
            return $this->redirectTo($this->loginRoute);
        }

        $userId = $claims['sub'];
        $administrator = Administrator::query()
            ->where(['id' => (int) $userId])
            ->active()
            ->one();

        if ($administrator === null) {
            return $this->redirectTo($this->loginRoute);
        }

        // Override locale if administrator has one set
        $adminLocale = $administrator->getLocale();
        if ($adminLocale !== null && $adminLocale !== '') {
            $this->translator->setLocale($adminLocale);
        }

        if (!empty($this->requiredPermissions)) {
            $hasPermission = false;
            foreach ($this->requiredPermissions as $permission) {
                if ($this->rbacManager->userHasPermission($userId, $permission)) {
                    $hasPermission = true;
                    break;
                }
            }
            if (!$hasPermission) {
                return $this->responseFactory->createResponse(Status::FORBIDDEN);
            }
        }

        ViewInjection::setUserId($userId);

        $request = $request->withAttribute('userId', $userId);
        $request = $request->withAttribute('scopes', $claims['scopes']);
        $request = $request->withAttribute('administrator', $administrator);

        $response = $handler->handle($request);

        if ($freshAccessToken !== null) {
            $accessCookie = new Cookie(self::COOKIE_ACCESS_TOKEN, $freshAccessToken);
            $accessCookie = $accessCookie
                ->withPath($this->dboardConfig->routePrefix)
                ->withSameSite(Cookie::SAME_SITE_LAX)
                ->withSecure(true)
                ->withHttpOnly(false);
            $response = $accessCookie->addToResponse($response);
        }

        if ($freshRefreshToken !== null) {
            $refreshCookie = new Cookie(self::COOKIE_REFRESH_TOKEN, $freshRefreshToken);
            $refreshCookie = $refreshCookie
                ->withPath($this->dboardConfig->routePrefix)
                ->withSameSite(Cookie::SAME_SITE_LAX)
                ->withSecure(true)
                ->withHttpOnly(true);
            $response = $refreshCookie->addToResponse($response);
        }

        return $response;
    }

    private function extractTokens(ServerRequestInterface $request): array
    {
        $cookies = $request->getCookieParams();
        $tokens = [
            self::COOKIE_ACCESS_TOKEN => ($cookies[self::COOKIE_ACCESS_TOKEN] ?? null),
            self::COOKIE_REFRESH_TOKEN => ($cookies[self::COOKIE_REFRESH_TOKEN] ?? null),
        ];

        $header = $this->extractBearerToken($request);
        if ($header !== null) {
            $tokens[self::COOKIE_ACCESS_TOKEN] = $header;
        }
        return $tokens;
    }

    /**
     * @return array{sub: string, iss: string, aud: string, scopes: string[]}|null
     */
    private function validateAndParseToken(?string $token): ?array
    {
        if ($token === null) {
            return null;
        }

        $preClaims = $this->parseTokenClaims($token);
        if ($preClaims === null || $preClaims['iss'] !== $this->cypherKey->getId()) {
            $this->logger->debug('JWT validation failed: issuer mismatch', [
                'expected' => $this->cypherKey->getId(),
                'got' => $preClaims['iss'] ?? null,
            ]);
            return null;
        }

        $claims = $this->validateToken($token, $this->cypherKey);
        if ($claims === null) {
            $this->logger->debug('JWT validation failed: constraints not met');
            return null;
        }

        return $claims;
    }

    /**
     * @return array{access_token: string, refresh_token?: string}|null
     */
    private function refreshAccessToken(string $refreshToken): ?array
    {
        try {
            $this->logger->debug('Attempting to refresh access token');

            $config = new PopulationConfig(
                name: 'dboard',
                issuer: $this->cypherKey->getId(),
                audience: 'dboard',
                userQueryClass: Administrator::class,
                clientQueryClass: Client::class,
                refreshTokenQueryClass: RefreshToken::class,
                cypherKeyQueryClass: CypherKey::class,
                accessTokenTtl: 60 * 60, // 1 hour
                refreshTokenTtl: 60 * 60 * 24 * 15, // 15 days
                allowedGrants: ['password', 'refresh_token'],
            );

            $storage = new Oauth2Storage(
                userClass: $config->userQueryClass,
                clientClass: $config->clientQueryClass,
                refreshTokenClass: $config->refreshTokenQueryClass,
                scopeProvider: $this->scopeProvider,
                cypherKeyClass: $config->cypherKeyQueryClass,
                logger: $this->logger,
            );

            $server = Oauth2ServerFactory::create($storage, $config);

            $tokenRequest = new Oauth2Request(
                query: [],
                request: [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                    'client_id' => 'dboard',
                ],
                attributes: [],
                cookies: [],
                files: [],
                server: ['REQUEST_METHOD' => 'POST']
            );

            $tokenResponse = new Oauth2Response();
            $server->handleTokenRequest($tokenRequest, $tokenResponse);

            $statusCode = $tokenResponse->getStatusCode();
            $responseBody = $tokenResponse->getResponseBody();

            $this->logger->debug('Refresh token response', [
                'statusCode' => $statusCode,
                'body' => $responseBody,
            ]);

            if ($statusCode === 200) {
                $tokenData = json_decode($responseBody, true);
                if (isset($tokenData['access_token'])) {
                    $this->logger->debug('Refresh token successful');
                    return $tokenData;
                }
            }

            $this->logger->debug('Refresh token failed', [
                'statusCode' => $statusCode,
                'body' => $responseBody,
            ]);

            return null;
        } catch (Throwable $e) {
            $this->logger->error('Refresh token exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    private function redirectTo(string $route): ResponseInterface
    {
        $url = $this->urlGenerator->generate($route);
        return $this->responseFactory
            ->createResponse(Status::FOUND)
            ->withHeader(Header::LOCATION, $url);
    }
}
