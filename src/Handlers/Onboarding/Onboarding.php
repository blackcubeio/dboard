<?php

declare(strict_types=1);

/**
 * Onboarding.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Onboarding;

use Blackcube\Dboard\Handlers\Commons\AbstractSessionHandler;
use Blackcube\Dboard\Models\Administrator;

/**
 * Onboarding abstract action.
 */
abstract class Onboarding extends AbstractSessionHandler
{
    protected const SESSION_KEY = 'blap_onboarding';
    protected const SESSION_ONBOARDING = 'blap_onboarding_active';
    protected const SESSION_ADMIN_ID = 'blap_onboarding_admin_id';

    protected function getLayout(): string
    {
        return $this->aliases->get($this->dboardConfig->viewsAlias . '/Layouts/onboarding.php');
    }

    protected function isOnboardingRequired(): bool
    {
        return Administrator::query()->count() === 0;
    }
}
