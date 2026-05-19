<?php

declare(strict_types=1);

/**
 * PasskeysInit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Account;

use Blackcube\Dboard\Handlers\Commons\AbstractAjaxHandler;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Passkey;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Status;
use Yiisoft\Router\CurrentRoute;

/**
 * Passkeys drawer init (GET only).
 * Displays the list of passkeys for the authenticated administrator.
 */
final class PasskeysInit extends AbstractAjaxHandler
{
    /** @var Passkey[] */
    private array $passkeys = [];

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

        // Load passkeys
        $this->passkeys = Passkey::query()
            ->with('passkeyDevice')
            ->andWhere(['administratorId' => (int) $administrator->getId()])
            ->active()
            ->orderBy(['dateCreate' => SORT_DESC])
            ->all();

        return null;
    }

    protected function handleMethod(): void
    {
        // GET only — nothing to handle
    }

    protected function prepareOutputData(): array
    {
        $administrator = $this->models['main'];

        // Ajaxify refresh - return partial content only
        if ($this->isAjaxify()) {
            return [
                'type' => OutputType::Partial->value,
                'view' => 'Account/_passkeys-list-content',
                'data' => [
                    'administrator' => $administrator,
                    'passkeys' => $this->passkeys,
                    'urlGenerator' => $this->urlGenerator,
                ],
            ];
        }

        // GET - display full drawer
        $header = (string) $this->renderPartial('Commons/_drawer-header', [
            'title' => $this->translator->translate('Passkeys', category: 'dboard-modules'),
            'uiColor' => UiColor::Primary,
        ])->getBody();

        $content = (string) $this->renderPartial('Account/_passkeys-content', [
            'administrator' => $administrator,
            'passkeys' => $this->passkeys,
            'urlGenerator' => $this->urlGenerator,
            'currentRoute' => $this->currentRoute,
        ])->getBody();

        return [
            'type' => OutputType::Json->value,
            'data' => [
                ...AureliaCommunication::dialog(DialogAction::Keep),
                ...AureliaCommunication::dialogContent($header, $content, UiColor::Primary),
            ],
        ];
    }
}
