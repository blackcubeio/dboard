<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Oauth;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Handlers\Commons\AbstractBaseHandler;
use Blackcube\Dboard\Services\AddonPopulations;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Status;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Authorization server metadata of an addon population (RFC 8414), served
 * under /.well-known/oauth-authorization-server followed by the path of the
 * population, its issuer.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Metadata extends AbstractBaseHandler
{
    private const AUTHORIZE_SUFFIX = '/authorize';

    public function __construct(
        LoggerInterface $logger,
        DboardConfig $dboardConfig,
        WebViewRenderer $viewRenderer,
        ResponseFactoryInterface $responseFactory,
        JsonResponseFactory $jsonResponseFactory,
        UrlGeneratorInterface $urlGenerator,
        Aliases $aliases,
        TranslatorInterface $translator,
        protected CurrentRoute $currentRoute,
        protected AddonPopulations $populations,
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
        $population = $this->populations->get((string) $this->currentRoute->getArgument('population'));
        if ($population === null) {
            $metadataResponse = $this->responseFactory->createResponse(Status::NOT_FOUND);
        } else {
            $arguments = [
                'population' => $population->name,
            ];
            $authorizationEndpoint = $this->urlGenerator->generateAbsolute('dboard.oauth.authorize', $arguments);
            $metadataResponse = $this->renderJson([
                'issuer' => substr($authorizationEndpoint, 0, -strlen(self::AUTHORIZE_SUFFIX)),
                'authorization_endpoint' => $authorizationEndpoint,
                'token_endpoint' => $this->urlGenerator->generateAbsolute('dboard.oauth.token', $arguments),
                'registration_endpoint' => $this->urlGenerator->generateAbsolute('dboard.oauth.register', $arguments),
                'scopes_supported' => $population->scopes,
                'response_types_supported' => [
                    'code',
                ],
                'grant_types_supported' => [
                    'authorization_code',
                    'refresh_token',
                ],
                'code_challenge_methods_supported' => [
                    'S256',
                ],
                'token_endpoint_auth_methods_supported' => [
                    'none',
                ],
            ]);
        }
        return $metadataResponse;
    }
}
