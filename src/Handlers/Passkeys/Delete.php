<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Passkeys;

use Blackcube\Dboard\Handlers\Commons\AbstractDelete;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Passkey;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Enums\UiLevel;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * Passkey delete action.
 * User can only delete their own passkeys.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Delete extends AbstractDelete
{
    protected function getModelClass(): string { return Passkey::class; }
    protected function getEntityName(): string { return 'passkey'; }
    protected function getEntityLabel(): string { return 'passkey'; }
    protected function getListId(): string { return 'passkeys-list'; }
    protected function getListRoute(): string { return 'dboard.passkeys'; }
    protected function getSuccessMessage(): string { return $this->translator->translate('Passkey deleted.', category: 'dboard-modules'); }

    protected function getModelName(): string
    {
        return $this->models['main']->getName();
    }

    private function isOwner(): bool
    {
        /** @var Administrator|null $administrator */
        $administrator = $this->request->getAttribute('administrator');

        return $administrator !== null && $this->models['main']->getAdministratorId() === (int) $administrator->getId();
    }

    protected function handleMethod(): void
    {
        if ($this->request->getMethod() === Method::DELETE && $this->isOwner() === true) {
            $this->models['main']->delete();
        }
    }

    protected function prepareOutputData(): array
    {
        if ($this->isOwner() === false) {
            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Close),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Error', category: 'dboard-modules'),
                        $this->translator->translate('You can only delete your own passkeys.', category: 'dboard-modules'),
                        UiLevel::Danger
                    ),
                ],
            ];
        } elseif ($this->request->getMethod() === Method::DELETE) {
            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Close),
                    ...AureliaCommunication::ajaxify(
                        $this->getListId(),
                        $this->urlGenerator->generate($this->getListRoute())
                    ),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-modules'),
                        $this->getSuccessMessage(),
                        UiLevel::Success
                    ),
                ],
            ];
        } else {
            $content = (string) $this->renderPartial('Commons/_delete-content', [
                'modelName' => $this->getModelName(),
                'entityLabel' => $this->getEntityLabel(),
                'urlGenerator' => $this->urlGenerator,
                'formAction' => $this->urlGenerator->generate(
                    $this->currentRoute->getName(),
                    $this->getModelRouteParams()
                ),
            ])->getBody();

            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Keep),
                    ...AureliaCommunication::dialogContent(Bleet::dialogFrame(DialogType::Modal)
                        ->title($this->translator->translate('Deletion', category: 'dboard-modules'))
                        ->danger()
                        ->content($content)
                        ->render()),
                ],
            ];
        }

        return $outputData;
    }
}
