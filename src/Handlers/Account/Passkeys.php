<?php

declare(strict_types=1);

/**
 * Passkeys.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Account;

use Blackcube\Dboard\DboardConfig;
use Blackcube\Dboard\Handlers\Commons\AbstractAjaxHandler;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Components\WebauthnHelper;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Passkey;
use Blackcube\Dboard\Services\WebauthnConfig;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\SessionInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Passkeys management action (POST = create, DELETE = remove).
 * POST receives WebAuthn credential data from the browser.
 * DELETE removes a passkey by its credential ID.
 */
final class Passkeys extends AbstractAjaxHandler
{
    private bool $saved = false;
    private bool $deleted = false;

    public function __construct(
        LoggerInterface $logger,
        DboardConfig $dboardConfig,
        WebViewRenderer $viewRenderer,
        ResponseFactoryInterface $responseFactory,
        JsonResponseFactory $jsonResponseFactory,
        UrlGeneratorInterface $urlGenerator,
        Aliases $aliases,
        TranslatorInterface $translator,
        CurrentRoute $currentRoute,
        protected SessionInterface $session,
        protected WebauthnConfig $webauthnConfig,
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
            currentRoute: $currentRoute,
        );
    }

    protected function getActionModels(): array
    {
        return [
            'main' => new ActionModel(
                modelClass: Administrator::class,
                formModelClass: null,
                isMain: true,
            ),
        ];
    }

    protected function setupAction(): ?ResponseInterface
    {
        $response = parent::setupAction();
        if ($response !== null) {
            return $response;
        }

        // Self-only check
        $currentUserId = $this->request->getAttribute('userId');
        $administrator = $this->models['main'];
        if ($currentUserId === null || $currentUserId !== $administrator->getId()) {
            return $this->responseFactory->createResponse(Status::NOT_FOUND);
        }

        return null;
    }

    protected function handleMethod(): void
    {
        $method = $this->request->getMethod();

        if ($method === Method::POST) {
            $this->handleCreate();
        } elseif ($method === Method::DELETE) {
            $this->handleDelete();
        }
    }

    private function handleCreate(): void
    {
        $body = $this->getBodyParams();
        if (!is_array($body) || !isset($body['id'], $body['rawId'], $body['response'], $body['type'])) {
            throw new \RuntimeException('Invalid WebAuthn data.');
        }

        // Extraire le nom avant de passer au sérialiseur
        $name = $body['name'] ?? 'Key ' . date('d/m/Y H:i');
        unset($body['name']);

        // Récupérer les options de création depuis la session
        $creationOptionsData = $this->session->get('webauthn_creation_options');
        if ($creationOptionsData === null) {
            throw new \RuntimeException('WebAuthn options expired.');
        }
        $this->session->remove('webauthn_creation_options');
        $this->session->remove('webauthn_challenge');

        /** @var PublicKeyCredentialCreationOptions $creationOptions */
        $creationOptions = WebauthnHelper::toObject(
            $creationOptionsData,
            PublicKeyCredentialCreationOptions::class
        );

        // Désérialiser la réponse du navigateur
        /** @var PublicKeyCredential $publicKeyCredential */
        $publicKeyCredential = WebauthnHelper::toObject($body, PublicKeyCredential::class);

        // Valider l'attestation via webauthn-lib
        $host = $this->webauthnConfig->getRpId($this->request);
        $validator = WebauthnHelper::getAttestationValidator();
        $source = $validator->check($publicKeyCredential->response, $creationOptions, $host);

        // Sauvegarder via WebauthnHelper::savePasskey()
        $administrator = $this->models['main'];
        $transaction = $administrator->db()->beginTransaction();
        try {
            WebauthnHelper::savePasskey($source, $name, (int) $administrator->getId());
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->saved = true;
    }

    private function handleDelete(): void
    {
        $body = $this->getBodyParams();
        $passkeyId = $body['passkeyId'] ?? null;

        if ($passkeyId === null) {
            throw new \RuntimeException('Missing passkey ID.');
        }

        $administrator = $this->models['main'];

        $passkey = Passkey::query()
            ->andWhere([
                'id' => $passkeyId,
                'administratorId' => (int) $administrator->getId(),
            ])
            ->one();

        if ($passkey === null) {
            throw new \RuntimeException('Passkey not found.');
        }

        $transaction = $passkey->db()->beginTransaction();
        try {
            $passkey->delete();
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->deleted = true;
    }

    protected function prepareOutputData(): array
    {
        if ($this->saved || $this->deleted) {
            $refreshUrl = $this->urlGenerator->generate(
                'dboard.account.passkeys.init',
                $this->extractPrimaryKeysFromModel()
            );
            $message = $this->saved
                ? $this->translator->translate('Passkey added.', category: 'dboard-modules')
                : $this->translator->translate('Passkey deleted.', category: 'dboard-modules');

            return [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Keep),
                    ...AureliaCommunication::toast($this->translator->translate('Success', category: 'dboard-modules'), $message, UiColor::Success),
                    ...AureliaCommunication::ajaxify('passkeys-list', $refreshUrl),
                ],
            ];
        }

        // Fallback (ne devrait pas arriver sur POST/DELETE)
        return [
            'type' => OutputType::Json->value,
            'data' => [],
        ];
    }
}