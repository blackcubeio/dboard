<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Hosts;

use Blackcube\Dcore\Models\Host;
use Blackcube\Dboard\Handlers\Commons\AbstractToggle;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiLevel;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Yiisoft\Router\CurrentRoute;

/**
 * Host toggle action.
 * Host id=1 is protected and cannot be toggled.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Toggle extends AbstractToggle
{
    protected function getModelClass(): string { return Host::class; }
    protected function getEntityName(): string { return 'host'; }
    protected function getListId(): string { return 'hosts-list'; }
    protected function getListRoute(): string { return 'dboard.hosts'; }

    protected function getSuccessMessage(): string
    {
        return $this->translator->translate('Host "{name}" has been {status, select, enabled{enabled} other{disabled}}.', [
            'name' => $this->getModelName(),
            'status' => $this->models['main']->isActive() === true ? 'enabled' : 'disabled',
        ], 'dboard-modules');
    }

    protected function prepareOutputData(): array
    {
        if ($this->models['main']->getId() === 1) {
            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Close),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Error', category: 'dboard-modules'),
                        $this->translator->translate('The default host cannot be modified.', category: 'dboard-modules'),
                        UiLevel::Danger
                    ),
                ],
            ];
        } else {
            $outputData = parent::prepareOutputData();
        }

        return $outputData;
    }
}
