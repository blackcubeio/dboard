<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Account;

use Blackcube\Dboard\Handlers\Commons\AbstractAjaxHandler;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Passkey;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
use Yiisoft\Http\Status;
use Yiisoft\Router\CurrentRoute;

/**
 * Passkeys drawer init (GET only).
 * Displays the list of passkeys for the authenticated administrator.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class PasskeysInit extends AbstractAjaxHandler
{
    private ?ActiveQueryInterface $passkeysQuery = null;

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
        $setupResponse = parent::setupAction();
        if ($setupResponse === null) {
            $currentUserId = $this->request->getAttribute('userId');
            $administrator = $this->models['main'];
            if ($currentUserId === null || $currentUserId !== $administrator->getId()) {
                $setupResponse = $this->responseFactory->createResponse(Status::NOT_FOUND);
            } else {
                $this->passkeysQuery = Passkey::query()
                    ->with('passkeyDevice')
                    ->andWhere([
                        'administratorId' => (int) $administrator->getId(),
                    ])
                    ->active()
                    ->orderBy([
                        'dateCreate' => SORT_DESC,
                        'id' => SORT_DESC,
                    ]);
            }
        }

        return $setupResponse;
    }

    protected function handleMethod(): void
    {
    }

    protected function prepareOutputData(): array
    {
        $administrator = $this->models['main'];

        if ($this->isAjaxify() === true) {
            $outputData = [
                'type' => OutputType::Partial->value,
                'view' => 'Account/_passkeys-list-content',
                'data' => [
                    'administrator' => $administrator,
                    'passkeysQuery' => $this->passkeysQuery,
                    'urlGenerator' => $this->urlGenerator,
                ],
            ];
        } else {
            $content = (string) $this->renderPartial('Account/_passkeys-content', [
                'administrator' => $administrator,
                'passkeysQuery' => $this->passkeysQuery,
                'urlGenerator' => $this->urlGenerator,
                'currentRoute' => $this->currentRoute,
            ])->getBody();

            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Keep),
                    ...AureliaCommunication::dialogContent(Bleet::dialogFrame(DialogType::Drawer)
                        ->title($this->translator->translate('Passkeys', category: 'dboard-modules'))
                        ->primary()
                        ->content($content)
                        ->render()),
                ],
            ];
        }

        return $outputData;
    }
}
