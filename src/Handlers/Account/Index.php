<?php

declare(strict_types=1);

/**
 * Index.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Account;

use Blackcube\Dboard\Handlers\Commons\AbstractEdit;
use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\AccountForm;
use Blackcube\Dboard\Services\LocaleHelper;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Status;
use Yiisoft\Router\CurrentRoute;

/**
 * Account index action.
 * Admin edits their own profile (name, email, password).
 * Inherits AbstractEdit pattern but with self-only access control.
 */
final class Index extends AbstractEdit
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
        $response = parent::setupAction();
        if ($response !== null) {
            return $response;
        }

        // Self-only: verify route {id} matches JWT userId
        $currentUserId = $this->request->getAttribute('userId');
        $administrator = $this->models['main'];

        if ($currentUserId === null || $currentUserId !== $administrator->getId()) {
            return $this->responseFactory->createResponse(Status::NOT_FOUND);
        }

        return null;
    }

    protected function prepareOutputData(): array
    {
        $output = parent::prepareOutputData();
        if (isset($output['data'])) {
            $output['data']['localeOptions'] = LocaleHelper::getOptions();
        }
        return $output;
    }
}
