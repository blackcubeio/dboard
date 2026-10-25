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
use Blackcube\Dboard\Services\AddonPopulation;
use Blackcube\Dboard\Services\AddonPopulations;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Status;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Dynamic client registration of an addon population (RFC 7591). Only public
 * clients are registered: they prove themselves with PKCE, so no secret is
 * issued. The redirect URIs are absolute https, or http on a loopback host.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Register extends AbstractBaseHandler
{
    private const GRANT_AUTHORIZATION_CODE = 'authorization_code';
    private const GRANT_REFRESH_TOKEN = 'refresh_token';
    private const RESPONSE_TYPE_CODE = 'code';
    private const AUTH_METHOD_NONE = 'none';
    private const DEFAULT_NAME = 'OAuth client';
    private const LOOPBACK_HOSTS = [
        'localhost',
        '127.0.0.1',
        '[::1]',
    ];

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
            $registerResponse = $this->responseFactory->createResponse(Status::NOT_FOUND);
        } else {
            $body = $this->getBodyParams();
            $metadata = is_array($body) === true ? $body : [];
            $error = $this->validateMetadata($metadata);
            if ($error !== null) {
                $registerResponse = $this->renderJson($error)->withStatus(Status::BAD_REQUEST);
            } else {
                $client = $this->register($population, $metadata);
                $issuedAt = time();
                if ($client->getDateCreate() !== null) {
                    $issuedAt = $client->getDateCreate()->getTimestamp();
                }
                $registerResponse = $this->renderJson([
                    'client_id' => $client->getId(),
                    'client_id_issued_at' => $issuedAt,
                    'client_name' => $client->getName(),
                    'redirect_uris' => $client->getRedirectUris(),
                    'grant_types' => $client->getAllowedGrants(),
                    'response_types' => [
                        self::RESPONSE_TYPE_CODE,
                    ],
                    'token_endpoint_auth_method' => self::AUTH_METHOD_NONE,
                ])->withStatus(Status::CREATED);
            }
        }
        return $registerResponse;
    }

    /**
     * @param array<string, mixed> $metadata
     * @return array{error: string, error_description: string}|null
     */
    private function validateMetadata(array $metadata): ?array
    {
        $error = null;
        $redirectUris = $metadata['redirect_uris'] ?? null;
        $grants = [
            self::GRANT_AUTHORIZATION_CODE,
        ];
        if (isset($metadata['grant_types']) === true) {
            $grants = $metadata['grant_types'];
        }
        $responseTypes = [
            self::RESPONSE_TYPE_CODE,
        ];
        if (isset($metadata['response_types']) === true) {
            $responseTypes = $metadata['response_types'];
        }
        $authMethod = self::AUTH_METHOD_NONE;
        if (isset($metadata['token_endpoint_auth_method']) === true) {
            $authMethod = $metadata['token_endpoint_auth_method'];
        }
        $allowedGrants = [
            self::GRANT_AUTHORIZATION_CODE,
            self::GRANT_REFRESH_TOKEN,
        ];
        if (is_array($redirectUris) === false || empty($redirectUris) === true) {
            $error = $this->error('invalid_redirect_uri', 'redirect_uris is required.');
        } elseif ($this->validRedirectUris($redirectUris) === false) {
            $error = $this->error('invalid_redirect_uri', 'redirect_uris must be absolute https URIs, or http on a loopback host, without fragment.');
        } elseif ($authMethod !== self::AUTH_METHOD_NONE) {
            $error = $this->error('invalid_client_metadata', 'Only public clients are registered: token_endpoint_auth_method must be none.');
        } elseif (is_array($grants) === false || empty($grants) === true || empty(array_diff($grants, $allowedGrants)) === false || in_array(self::GRANT_AUTHORIZATION_CODE, $grants, true) === false) {
            $error = $this->error('invalid_client_metadata', 'grant_types must be authorization_code, optionally with refresh_token.');
        } elseif (is_array($responseTypes) === false || empty(array_diff($responseTypes, [
            self::RESPONSE_TYPE_CODE,
        ])) === false) {
            $error = $this->error('invalid_client_metadata', 'response_types must be code.');
        }
        return $error;
    }

    /**
     * @return array{error: string, error_description: string}
     */
    private function error(string $error, string $description): array
    {
        return [
            'error' => $error,
            'error_description' => $description,
        ];
    }

    /**
     * @param array<int, mixed> $redirectUris
     */
    private function validRedirectUris(array $redirectUris): bool
    {
        $valid = true;
        foreach ($redirectUris as $redirectUri) {
            $parts = false;
            if (is_string($redirectUri) === true) {
                $parts = parse_url($redirectUri);
            }
            if ($parts === false || isset($parts['scheme'], $parts['host']) === false || isset($parts['fragment']) === true) {
                $valid = false;
            } elseif ($parts['scheme'] !== 'https' && ($parts['scheme'] !== 'http' || in_array($parts['host'], self::LOOPBACK_HOSTS, true) === false)) {
                $valid = false;
            }
        }
        return $valid;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function register(AddonPopulation $population, array $metadata): OauthClient
    {
        $name = trim((string) ($metadata['client_name'] ?? ''));
        if ($name === '') {
            $name = self::DEFAULT_NAME;
        }
        $client = new OauthClient();
        $client->setId(bin2hex(random_bytes(16)));
        $client->setPopulation($population->name);
        $client->setName(mb_substr($name, 0, 255));
        $grants = [
            self::GRANT_AUTHORIZATION_CODE,
        ];
        if (isset($metadata['grant_types']) === true) {
            $grants = array_values($metadata['grant_types']);
        }
        $client->setRedirectUris(array_values($metadata['redirect_uris']));
        $client->setAllowedGrants($grants);

        $transaction = $client->db()->beginTransaction();
        try {
            $client->save();
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return $client;
    }
}
