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
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\LoginForm;
use Blackcube\Dboard\Services\Client;
use Blackcube\Dboard\Services\RefreshToken;
use Blackcube\Oauth2\PopulationConfig;
use Blackcube\Oauth2\Server\Oauth2ServerFactory;
use Blackcube\Oauth2\Storage\Oauth2Storage;
use OAuth2\Request as Oauth2Request;
use OAuth2\Response as Oauth2Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Cookies\Cookie;
use Yiisoft\Http\Header;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;

/**
 * Login action.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Login extends AbstractBaseHandler
{
    protected function getLayout(): string
    {
        return $this->aliases->get($this->dboardConfig->viewsAlias . '/Layouts/guest.php');
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $loginResponse = null;

        if (Administrator::query()->count() === 0) {
            $loginResponse = $this->redirect('dboard.onboarding.step1');
        } else {
            $model = new LoginForm(translator: $this->translator);
            $model->setScenario('login');

            if ($request->getMethod() === Method::POST) {
                $postData = $request->getParsedBody();
                if ($model->load($postData) === true && $model->validate() === true) {
                    $rememberMe = $model->isRememberMe();

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
                        allowedGrants: [
                            'password',
                            'refresh_token',
                        ],
                    );

                    $storage = new Oauth2Storage(
                        userClass: $config->userQueryClass,
                        clientClass: $config->clientQueryClass,
                        refreshTokenClass: $config->refreshTokenQueryClass,
                        scopeProvider: $this->dboardConfig->scopeProvider,
                        cypherKeyClass: $config->cypherKeyQueryClass,
                        logger: $this->logger,
                    );

                    $server = Oauth2ServerFactory::create($storage, $config);

                    $tokenRequest = new Oauth2Request(
                        query: [],
                        request: [
                            'grant_type' => 'password',
                            'username' => $model->getEmail(),
                            'password' => $model->getPassword(),
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

                    if ($tokenResponse->getStatusCode() === 200) {
                        $tokenData = json_decode($tokenResponse->getResponseBody(), true);

                        if (isset($tokenData['access_token']) === true) {
                            $returnUrl = $this->returnUrl();
                            if ($returnUrl === null) {
                                $loginResponse = $this->redirect('dboard.dashboard');
                            } else {
                                $loginResponse = $this->responseFactory
                                    ->createResponse(Status::FOUND)
                                    ->withHeader(Header::LOCATION, $returnUrl);
                            }

                            $accessCookie = new Cookie(JwtAuthMiddleware::COOKIE_ACCESS_TOKEN, $tokenData['access_token']);
                            $accessCookie = $accessCookie
                                ->withPath($this->dboardConfig->routePrefix)
                                ->withSameSite(Cookie::SAME_SITE_LAX)
                                ->withSecure(true)
                                ->withHttpOnly(false);

                            $loginResponse = $accessCookie->addToResponse($loginResponse);

                            if ($rememberMe === true && isset($tokenData['refresh_token']) === true) {
                                $refreshCookie = new Cookie(JwtAuthMiddleware::COOKIE_REFRESH_TOKEN, $tokenData['refresh_token']);
                                $refreshCookie = $refreshCookie
                                    ->withPath($this->dboardConfig->routePrefix)
                                    ->withSameSite(Cookie::SAME_SITE_LAX)
                                    ->withSecure(true)
                                    ->withHttpOnly(true);

                                $loginResponse = $refreshCookie->addToResponse($loginResponse);
                            }
                        }
                    }

                    if ($loginResponse === null) {
                        $model->addError('Invalid credentials', [
                            'email',
                        ]);
                    }
                }
            }

            if ($loginResponse === null) {
                $loginResponse = $this->render('Authentication/login', [
                    'model' => $model,
                    'returnUrl' => $this->returnUrl(),
                ]);
            }
        }

        return $loginResponse;
    }

    /**
     * The page to come back to after login, when the login was reached from a
     * dboard page: a path under the route prefix and nothing else, so the
     * login never sends the browser outside.
     */
    private function returnUrl(): ?string
    {
        $returnUrl = null;
        $candidate = (string) ($this->request->getQueryParams()['redirect'] ?? '');
        if (str_starts_with($candidate, $this->dboardConfig->routePrefix . '/') === true && preg_match('/[\r\n]/', $candidate) !== 1) {
            $returnUrl = $candidate;
        }
        return $returnUrl;
    }
}
