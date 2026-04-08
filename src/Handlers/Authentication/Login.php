<?php

declare(strict_types=1);

/**
 * Login.php
 *
 * @copyright 2010-2026 Blackcube
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
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;

/**
 * Login action.
 */
final class Login extends AbstractBaseHandler
{
    protected function getLayout(): string
    {
        return $this->aliases->get($this->dboardConfig->viewsAlias . '/Layouts/guest.php');
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        // Redirect vers onboarding si pas d'admin
        if (Administrator::query()->count() === 0) {
            return $this->redirect('dboard.onboarding.step1');
        }

        $model = new LoginForm(translator: $this->translator);
        $model->setScenario('login');

        if ($request->getMethod() === Method::POST) {
            $postData = $request->getParsedBody();
            if ($model->load($postData) && $model->validate()) {
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
                    allowedGrants: ['password', 'refresh_token'],
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
                    server: ['REQUEST_METHOD' => 'POST']
                );

                $tokenResponse = new Oauth2Response();
                $server->handleTokenRequest($tokenRequest, $tokenResponse);

                if ($tokenResponse->getStatusCode() === 200) {
                    $tokenData = json_decode($tokenResponse->getResponseBody(), true);

                    if (isset($tokenData['access_token'])) {
                        $response = $this->redirect('dboard.dashboard');

                        $accessCookie = new Cookie(JwtAuthMiddleware::COOKIE_ACCESS_TOKEN, $tokenData['access_token']);
                        $accessCookie = $accessCookie
                            ->withPath($this->dboardConfig->routePrefix)
                            ->withSameSite(Cookie::SAME_SITE_LAX)
                            ->withSecure(true)
                            ->withHttpOnly(false);

                        $response = $accessCookie->addToResponse($response);

                        if ($rememberMe && isset($tokenData['refresh_token'])) {
                            $refreshCookie = new Cookie(JwtAuthMiddleware::COOKIE_REFRESH_TOKEN, $tokenData['refresh_token']);
                            $refreshCookie = $refreshCookie
                                ->withPath($this->dboardConfig->routePrefix)
                                ->withSameSite(Cookie::SAME_SITE_LAX)
                                ->withSecure(true)
                                ->withHttpOnly(true);

                            $response = $refreshCookie->addToResponse($response);
                        }

                        return $response;
                    }
                }

                $model->addError('Invalid credentials', ['email']);
            }
        }

        return $this->render('Authentication/login', [
            'model' => $model,
        ]);
    }
}
