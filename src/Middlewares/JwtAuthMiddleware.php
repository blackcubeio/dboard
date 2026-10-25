<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Middlewares;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Services\Client;
use Blackcube\Oauth2\Jwt\CypherKey;
use Blackcube\Dboard\Services\RefreshToken;
use Blackcube\Dboard\Services\ScopeProvider;
use Blackcube\Dboard\Services\ViewInjection;
use Blackcube\Oauth2\Middlewares\AbstractJwtMiddleware;
use Blackcube\Oauth2\PopulationConfig;
use Blackcube\Oauth2\Server\Oauth2ServerFactory;
use Blackcube\Oauth2\Storage\Oauth2Storage;
use OAuth2\Request as Oauth2Request;
use OAuth2\Response as Oauth2Response;
use Psr\Clock\ClockInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cookies\Cookie;
use Yiisoft\Http\Header;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Authenticates the administrator from the JWT cookies, refreshes the access token and checks the RBAC permissions of the route.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class JwtAuthMiddleware extends AbstractJwtMiddleware
{
    public const COOKIE_ACCESS_TOKEN = 'access_token';
    public const COOKIE_REFRESH_TOKEN = 'refresh_token';
    public const HIDE_TOKENS = true;

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
        private readonly WebViewRenderer $viewRenderer,
        private readonly Aliases $aliases,
    ) {
        parent::__construct($clock);
    }

    /**
     * Middleware definition of a route open to anonymous visitors: the administrator is attached when authenticated.
     */
    public static function optional(): array
    {
        return [
            'class' => self::class,
            'withOptional()' => [],
        ];
    }

    /**
     * Middleware definition of a route reserved to administrators holding one of the permissions.
     *
     * @param string|string[] $permissions
     */
    public static function withRbac(string|array $permissions): array
    {
        $requiredPermissions = $permissions;
        if (is_array($permissions) === false) {
            $requiredPermissions = [
                $permissions,
            ];
        }
        return [
            'class' => self::class,
            'withRequiredPermissions()' => [
                $requiredPermissions,
            ],
        ];
    }

    public function withOptional(): static
    {
        $new = clone $this;
        $new->optional = true;
        return $new;
    }

    /**
     * @param string[] $requiredPermissions
     */
    public function withRequiredPermissions(array $requiredPermissions): static
    {
        $new = clone $this;
        $new->requiredPermissions = $requiredPermissions;
        return $new;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $requestHandler
    ): ResponseInterface {
        $authResponse = null;
        $claims = null;
        $freshAccessToken = null;
        $freshRefreshToken = null;
        $tokens = $this->extractTokens($request);
        $accessToken = $tokens[self::COOKIE_ACCESS_TOKEN];
        $refreshToken = $tokens[self::COOKIE_REFRESH_TOKEN];

        if ($accessToken !== null || $refreshToken !== null) {
            $claims = $this->validateAndParseToken($accessToken);

            if ($claims === null && $refreshToken !== null) {
                $refreshResult = $this->refreshAccessToken($refreshToken);
                if ($refreshResult !== null) {
                    $freshAccessToken = $refreshResult['access_token'];
                    $freshRefreshToken = $refreshResult['refresh_token'] ?? null;
                    $claims = $this->validateAndParseToken($freshAccessToken);
                }
            }
        }

        if ($claims === null) {
            if ($this->optional === true) {
                $authResponse = $requestHandler->handle($request);
            } else {
                $authResponse = $this->redirectTo($this->loginRoute, $this->returnUrl($request));
            }
        } else {
            $userId = $claims['sub'];
            $administrator = Administrator::query()
                ->andWhere([
                    'id' => (int) $userId,
                ])
                ->active()
                ->one();

            if ($administrator === null) {
                $authResponse = $this->redirectTo($this->loginRoute, $this->returnUrl($request));
            } else {
                $adminLocale = $administrator->getLocale();
                if ($adminLocale !== null && $adminLocale !== '') {
                    $this->translator->setLocale($adminLocale);
                }

                $hasPermission = true;
                if (empty($this->requiredPermissions) === false) {
                    $hasPermission = false;
                    foreach ($this->requiredPermissions as $permission) {
                        if ($hasPermission === false && $this->rbacManager->userHasPermission($userId, $permission) === true) {
                            $hasPermission = true;
                        }
                    }
                }

                if ($hasPermission === false) {
                    $authResponse = $this->renderForbidden();
                } else {
                    ViewInjection::setUserId($userId);
                    ViewInjection::setAdministrator($administrator);

                    $request = $request->withAttribute('userId', $userId);
                    $request = $request->withAttribute('administrator', $administrator);

                    $authResponse = $requestHandler->handle($request);

                    if ($freshAccessToken !== null) {
                        $accessCookie = new Cookie(self::COOKIE_ACCESS_TOKEN, $freshAccessToken);
                        $accessCookie = $accessCookie
                            ->withPath($this->dboardConfig->routePrefix)
                            ->withSameSite(Cookie::SAME_SITE_LAX)
                            ->withSecure(true)
                            ->withHttpOnly(false);
                        $authResponse = $accessCookie->addToResponse($authResponse);
                    }

                    if ($freshRefreshToken !== null) {
                        $refreshCookie = new Cookie(self::COOKIE_REFRESH_TOKEN, $freshRefreshToken);
                        $refreshCookie = $refreshCookie
                            ->withPath($this->dboardConfig->routePrefix)
                            ->withSameSite(Cookie::SAME_SITE_LAX)
                            ->withSecure(true)
                            ->withHttpOnly(true);
                        $authResponse = $refreshCookie->addToResponse($authResponse);
                    }
                }
            }
        }

        return $authResponse;
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
        $claims = null;
        if ($token !== null) {
            $preClaims = $this->parseTokenClaims($token);
            if ($preClaims === null || $preClaims['iss'] !== $this->cypherKey->getId()) {
                $this->logger->debug('JWT validation failed: issuer mismatch', [
                    'expected' => $this->cypherKey->getId(),
                    'got' => $preClaims['iss'] ?? null,
                ]);
            } else {
                $claims = $this->validateToken($token, $this->cypherKey);
                if ($claims === null) {
                    $this->logger->debug('JWT validation failed: constraints not met');
                }
            }
        }

        return $claims;
    }

    /**
     * @return array{access_token: string, refresh_token?: string}|null
     */
    private function refreshAccessToken(string $refreshToken): ?array
    {
        $tokenData = null;
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
                accessTokenTtl: 60 * 60,
                refreshTokenTtl: 60 * 60 * 24 * 15,
                allowedGrants: [
                    'password',
                    'refresh_token',
                ],
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
                server: [
                    'REQUEST_METHOD' => 'POST',
                ]
            );

            $tokenResponse = new Oauth2Response();
            $server->handleTokenRequest($tokenRequest, $tokenResponse);

            $statusCode = $tokenResponse->getStatusCode();
            $responseBody = $tokenResponse->getResponseBody();

            $this->logger->debug('Refresh token response', [
                'statusCode' => $statusCode,
                'body' => $this->maskTokens($responseBody),
            ]);

            if ($statusCode === 200) {
                $decodedBody = json_decode($responseBody, true);
                if (isset($decodedBody['access_token']) === true) {
                    $this->logger->debug('Refresh token successful');
                    $tokenData = $decodedBody;
                }
            }

            if ($tokenData === null) {
                $this->logger->debug('Refresh token failed', [
                    'statusCode' => $statusCode,
                    'body' => $this->maskTokens($responseBody),
                ]);
            }
        } catch (Throwable $e) {
            $this->logger->error('Refresh token exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $tokenData;
    }

    private function redirectTo(string $route, ?string $returnUrl = null): ResponseInterface
    {
        $query = [];
        if ($returnUrl !== null) {
            $query['redirect'] = $returnUrl;
        }
        $url = $this->urlGenerator->generate($route, [], $query);
        return $this->responseFactory
            ->createResponse(Status::FOUND)
            ->withHeader(Header::LOCATION, $url);
    }

    /**
     * The page to come back to after login: the current one, when it is a GET
     * the browser can replay.
     */
    private function returnUrl(ServerRequestInterface $request): ?string
    {
        $returnUrl = null;
        if ($request->getMethod() === Method::GET) {
            $returnUrl = $request->getRequestTarget();
        }
        return $returnUrl;
    }

    private function renderForbidden(): ResponseInterface
    {
        $viewPath = $this->aliases->get($this->dboardConfig->viewsAlias);
        $layout = $this->aliases->get($this->dboardConfig->viewsAlias . '/Layouts/guest.php');
        return $this->viewRenderer
            ->withViewPath($viewPath)
            ->withLayout($layout)
            ->render('Forbidden/index', [])
            ->withStatus(Status::FORBIDDEN);
    }

    /**
     * Keeps only the first characters of the access and refresh tokens of an OAuth2 response body, when HIDE_TOKENS is on.
     */
    private function maskTokens(string $responseBody): string
    {
        $loggedBody = $responseBody;
        $tokenNames = [
            'access_token',
            'refresh_token',
        ];
        if (self::HIDE_TOKENS === true) {
            $decodedBody = json_decode($responseBody, true);
            if (is_array($decodedBody) === true) {
                foreach ($tokenNames as $tokenName) {
                    if (isset($decodedBody[$tokenName]) === true) {
                        $decodedBody[$tokenName] = substr((string) $decodedBody[$tokenName], 0, 12) . '***';
                    }
                }
                $loggedBody = json_encode($decodedBody);
            }
        }
        return $loggedBody;
    }
}
