<?php

declare(strict_types=1);

/**
 * Edit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Hosts;

use Blackcube\Dcore\Models\Host;
use Blackcube\Dboard\Handlers\Commons\AbstractEdit;
use Blackcube\Dboard\Models\Forms\HostForm;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Router\CurrentRoute;

/**
 * Host edit action.
 * Host id=1 is protected: name and active cannot be modified.
 * Only siteName, siteAlternateName, siteDescription are editable.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Edit extends AbstractEdit
{
    protected function getModelClass(): string { return Host::class; }
    protected function getFormModelClass(): string { return HostForm::class; }
    protected function getEntityName(): string { return 'host'; }
    protected function getViewPrefix(): string { return 'Hosts'; }
    protected function getListRoute(): string { return 'dboard.hosts'; }

    protected function setupAction(): ?ResponseInterface
    {
        $response = parent::setupAction();
        if ($response !== null) {
            return $response;
        }

        // Host id=1 : seuls siteName, siteAlternateName, siteDescription sont éditables
        if ($this->models['main']->getId() === 1) {
            $this->formModels['main']->setScenario('edit-protected');
        }

        return null;
    }
}