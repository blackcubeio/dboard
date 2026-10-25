<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Oauth;

use Blackcube\Dboard\Components\Rbac;
use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Handlers\Commons\AbstractBaseHandler;
use Blackcube\Dboard\Models\Forms\ConsentForm;
use Blackcube\Dboard\Models\OauthClient;
use Blackcube\Dboard\Services\AddonPopulation;
use Blackcube\Dboard\Services\AddonPopulations;
use Blackcube\Oauth2\Handlers\Oauth2RequestTrait;
use OAuth2\Response as Oauth2Response;
use OAuth2\Server;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Authorization endpoint of an addon population (authorization_code + PKCE).
 *
 * GET validates the request through bshaffer and shows the consent screen: the
 * permissions published by the population, bounded by the permissions the
 * administrator holds. POST records the decision: the granted permissions are
 * checked again against that bound, the code is issued with the PKCE challenge
 * and the user agent goes back to the client.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Authorize extends AbstractBaseHandler
{
    use Oauth2RequestTrait;

    private const PKCE_METHOD = 'S256';
    private const PKCE_CHALLENGE_PATTERN = '/^[A-Za-z0-9\-._~]{43,128}$/';

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
        protected ManagerInterface $rbacManager,
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

    protected function getLayout(): string
    {
        return $this->aliases->get($this->dboardConfig->viewsAlias . '/Layouts/guest.php');
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $population = $this->populations->get((string) $this->currentRoute->getArgument('population'));
        if ($population === null) {
            $authorizeResponse = $this->responseFactory->createResponse(Status::NOT_FOUND);
        } else {
            $authorizeResponse = $this->authorize($population);
        }
        return $authorizeResponse;
    }

    private function authorize(AddonPopulation $population): ResponseInterface
    {
        $server = $population->getServer();
        $oauth2Request = $this->convertRequest($this->request);
        $oauth2Response = new Oauth2Response();
        if ($server->validateAuthorizeRequest($oauth2Request, $oauth2Response) === false) {
            $authorizeResponse = $this->convertResponse($oauth2Response);
        } else {
            $controller = $server->getAuthorizeController();
            $queryParams = $this->request->getQueryParams();
            $params = [
                'client_id' => (string) $controller->getClientId(),
                'redirect_uri' => (string) $controller->getRedirectUri(),
                'state' => $controller->getState(),
                'response_type' => (string) $controller->getResponseType(),
                'code_challenge' => (string) ($queryParams['code_challenge'] ?? ''),
                'code_challenge_method' => (string) ($queryParams['code_challenge_method'] ?? ''),
            ];
            $client = OauthClient::queryById($params['client_id']);
            if ($client === null || $client->getPopulation() !== $population->name) {
                $oauth2Response->setRedirect(Status::FOUND, $params['redirect_uri'], $params['state'], 'unauthorized_client', 'The client is not registered on this population.');
                $authorizeResponse = $this->convertResponse($oauth2Response);
            } elseif ($params['code_challenge_method'] !== self::PKCE_METHOD || preg_match(self::PKCE_CHALLENGE_PATTERN, $params['code_challenge']) !== 1) {
                $oauth2Response->setRedirect(Status::FOUND, $params['redirect_uri'], $params['state'], 'invalid_request', 'A PKCE S256 code_challenge is required.');
                $authorizeResponse = $this->convertResponse($oauth2Response);
            } else {
                $userId = (string) $this->request->getAttribute('userId');
                $offered = $this->offeredScopes($population, $userId, (string) $controller->getScope());
                if ($this->request->getMethod() === Method::POST) {
                    $authorizeResponse = $this->decide($server, $oauth2Response, $offered, $userId, $params);
                } else {
                    $authorizeResponse = $this->consent($population, $client, $offered);
                }
            }
        }
        return $authorizeResponse;
    }

    /**
     * The permissions the consent screen can offer: those published by the
     * population, held by the administrator, and asked by the client when it
     * named some.
     *
     * @return string[]
     */
    private function offeredScopes(AddonPopulation $population, string $userId, string $requestedScope): array
    {
        $userPermissions = $this->rbacManager->getPermissionsByUserId($userId);
        $held = [];
        foreach ($userPermissions as $permissionName => $permission) {
            $held[$permissionName] = $permission->getName();
        }
        $offered = array_intersect($population->scopes, $held);
        if ($requestedScope !== '') {
            $offered = array_intersect($offered, explode(' ', $requestedScope));
        }
        return array_values($offered);
    }

    /**
     * @param string[] $offered
     */
    private function consent(AddonPopulation $population, OauthClient $client, array $offered): ResponseInterface
    {
        $groups = [];
        foreach ($offered as $scope) {
            $groups[Rbac::extractRole($scope)][$scope] = Rbac::extractPermission($scope);
        }
        $form = new ConsentForm(translator: $this->translator);
        $form->setScopes($offered);
        return $this->render('Oauth/authorize', [
            'population' => $population,
            'client' => $client,
            'administrator' => $this->request->getAttribute('administrator'),
            'groups' => $groups,
            'form' => $form,
            'formAction' => $this->request->getRequestTarget(),
        ]);
    }

    /**
     * @param string[] $offered
     * @param array<string, string|null> $params
     */
    private function decide(Server $server, Oauth2Response $oauth2Response, array $offered, string $userId, array $params): ResponseInterface
    {
        $form = new ConsentForm(translator: $this->translator);
        $form->setOffered($offered);
        $body = $this->getBodyParams() ?? [];
        if ($form->load($body) === true && $form->validate() === true && $form->isAllowed() === true) {
            $params['scope'] = implode(' ', array_values(array_unique($form->getScopes())));
            [$redirectUri, $uriParams] = $server->getResponseType('code')->getAuthorizeResponse($params, $userId);
            $oauth2Response->setRedirect(Status::FOUND, $this->appendQuery((string) $redirectUri, $uriParams['query'] ?? []));
        } else {
            $oauth2Response->setRedirect(Status::FOUND, $params['redirect_uri'], $params['state'], 'access_denied', 'The administrator denied the request.');
        }
        return $this->convertResponse($oauth2Response);
    }

    /**
     * @param array<string, mixed> $query
     */
    private function appendQuery(string $uri, array $query): string
    {
        $url = $uri;
        if (empty($query) === false) {
            $separator = str_contains($uri, '?') === true ? '&' : '?';
            $url = $uri . $separator . http_build_query($query);
        }
        return $url;
    }
}
