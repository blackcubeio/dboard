<?php

declare(strict_types=1);

/**
 * Step2.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Onboarding;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Onboarding step 2 action.
 */
final class Step2 extends Onboarding
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        if ($this->session->get(self::SESSION_ONBOARDING) !== true) {
            return $this->redirect('dboard.onboarding.step1');
        }

        $sessionData = $this->session->get(self::SESSION_KEY, []);
        if (empty($sessionData)) {
            return $this->redirect('dboard.onboarding.step1');
        }

        return $this->render('Onboarding/step2', [
            'data' => $sessionData,
            'urlGenerator' => $this->urlGenerator,
        ]);
    }
}
