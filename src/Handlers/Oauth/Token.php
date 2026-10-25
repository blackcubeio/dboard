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
use Blackcube\Dboard\Models\OauthClient;
use Blackcube\Dboard\Services\AddonPopulations;
use Blackcube\Oauth2\Handlers\Oauth2RequestTrait;
use OAuth2\Response as Oauth2Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Status;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Token endpoint of an addon population: exchanges a code (with its PKCE
 * verifier) or a refresh token for the OAuth2 JSON response, as the client
 * expects it. The client must belong to the population.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Token extends AbstractBaseHandler
{
    use Oauth2RequestTrait;

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
            $tokenResponse = $this->responseFactory->createResponse(Status::NOT_FOUND);
        } else {
            $oauth2Request = $this->convertRequest($request);
            $oauth2Response = new Oauth2Response();
            $client = OauthClient::queryById((string) ($oauth2Request->request('client_id') ?? ''));
            if ($client === null || $client->getPopulation() !== $population->name) {
                $oauth2Response->setError(Status::BAD_REQUEST, 'invalid_client', 'The client is not registered on this population.');
            } else {
                $population->getServer()->handleTokenRequest($oauth2Request, $oauth2Response);
            }
            $tokenResponse = $this->convertResponse($oauth2Response);
        }
        return $tokenResponse;
    }
}
