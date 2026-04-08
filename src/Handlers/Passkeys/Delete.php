<?php

declare(strict_types=1);

/**
 * Delete.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Passkeys;

use Blackcube\Dboard\Handlers\Commons\AbstractDelete;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Passkey;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * Passkey delete action.
 * User can only delete their own passkeys.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Delete extends AbstractDelete
{
    protected function getModelClass(): string { return Passkey::class; }
    protected function getEntityName(): string { return 'passkey'; }
    protected function getEntityLabel(): string { return 'passkey'; }
    protected function getListId(): string { return 'passkeys-list'; }
    protected function getListRoute(): string { return 'dboard.passkeys'; }

    protected function getModelName(): string
    {
        return $this->models['main']->getName();
    }

    private function isOwner(): bool
    {
        /** @var Administrator|null $administrator */
        $administrator = $this->request->getAttribute('administrator');

        if ($administrator === null) {
            return false;
        }

        return $this->models['main']->getAdministratorId() === (int) $administrator->getId();
    }

    protected function handleMethod(): void
    {
        if ($this->request->getMethod() !== Method::DELETE) {
            return;
        }

        if (!$this->isOwner()) {
            return;
        }

        $this->models['main']->delete();
    }

    protected function prepareOutputData(): array
    {
        if (!$this->isOwner()) {
            return [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Close),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Error', category: 'dboard-modules'),
                        $this->translator->translate('You can only delete your own passkeys.', category: 'dboard-modules'),
                        UiColor::Danger
                    ),
                ],
            ];
        }

        if ($this->request->getMethod() === Method::DELETE) {
            return [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Close),
                    ...AureliaCommunication::ajaxify(
                        $this->getListId(),
                        $this->urlGenerator->generate($this->getListRoute())
                    ),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-modules'),
                        ucfirst($this->getEntityName()) . ' "' . $this->getModelName() . '" deleted.',
                        UiColor::Success
                    ),
                ],
            ];
        }

        $header = (string) $this->renderPartial('Commons/_modal-header', [
            'title' => $this->translator->translate('Deletion', category: 'dboard-modules'),
            'uiColor' => UiColor::Danger,
        ])->getBody();

        $content = (string) $this->renderPartial('Commons/_delete-content', [
            'modelName' => $this->getModelName(),
            'entityLabel' => $this->getEntityLabel(),
            'urlGenerator' => $this->urlGenerator,
            'formAction' => $this->urlGenerator->generate(
                $this->currentRoute->getName(),
                $this->getModelRouteParams()
            ),
        ])->getBody();

        return [
            'type' => OutputType::Json->value,
            'data' => [
                ...AureliaCommunication::dialog(DialogAction::Keep),
                ...AureliaCommunication::dialogContent($header, $content, UiColor::Danger),
            ],
        ];
    }
}
