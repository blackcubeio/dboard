<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Account;

use Blackcube\Dboard\Handlers\Commons\AbstractEdit;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\AccountForm;
use Blackcube\Dboard\Helpers\LocaleHelper;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Status;
use Yiisoft\Router\CurrentRoute;

/**
 * Account index action.
 * Admin edits their own profile (name, email, password).
 * Inherits AbstractEdit pattern but with self-only access control.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Index extends AbstractEdit
{
    protected function getModelClass(): string { return Administrator::class; }
    protected function getFormModelClass(): string { return AccountForm::class; }
    protected function getEntityName(): string { return 'administrator'; }
    protected function getViewPrefix(): string { return 'Account'; }
    protected function getListRoute(): string { return 'dboard.account'; }

    protected function getView(): string
    {
        return 'index';
    }

    /**
     * Stay on page after save (no list to redirect to).
     */
    protected function stayOnPageAfterSave(): bool
    {
        return true;
    }

    /**
     * Override to add self-only access control.
     * Route has {id} for homogeneity, but admin can ONLY access their own account.
     */
    protected function setupAction(): ?ResponseInterface
    {
        $setupResponse = parent::setupAction();
        if ($setupResponse === null) {
            $currentUserId = $this->request->getAttribute('userId');
            $administrator = $this->models['main'];

            if ($currentUserId === null || $currentUserId !== $administrator->getId()) {
                $setupResponse = $this->responseFactory->createResponse(Status::NOT_FOUND);
            }
        }

        return $setupResponse;
    }

    protected function prepareOutputData(): array
    {
        $outputData = parent::prepareOutputData();
        if (isset($outputData['data']) === true) {
            $outputData['data']['localeOptions'] = LocaleHelper::getOptions();
        }
        return $outputData;
    }
}
