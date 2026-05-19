<?php

declare(strict_types=1);

/**
 * Step1.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Onboarding;

use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\OnboardingForm;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Http\Method;

/**
 * Onboarding step 1 action.
 */
final class Step1 extends Onboarding
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        // If already in onboarding tunnel, continue
        // Otherwise check if onboarding is required (no admin exists)
        if ($this->session->get(self::SESSION_ONBOARDING) !== true && !$this->isOnboardingRequired()) {
            return $this->redirect('dboard.login');
        }

        // Mark onboarding as active
        $this->session->set(self::SESSION_ONBOARDING, true);

        $model = new OnboardingForm(translator: $this->translator);
        $model->setScenario('register');

        // Check if admin was created in step3 (ID stored in session)
        $adminId = $this->session->get(self::SESSION_ADMIN_ID);
        if ($adminId !== null) {
            $existingAdmin = Administrator::query()->andWhere(['id' => $adminId])->one();
            if ($existingAdmin !== null) {
                // Preload from DB — use confirm scenario (no password)
                $model->setScenario('confirm');
                $model->load([
                    $model->getFormName() => [
                        'email' => $existingAdmin->getEmail(),
                        'firstname' => $existingAdmin->getFirstname(),
                        'lastname' => $existingAdmin->getLastname(),
                    ],
                ]);
                $model->setScenario('register');
            }
        } else {
            // Restore from session if exists
            $sessionData = $this->session->get(self::SESSION_KEY, []);
            if (!empty($sessionData)) {
                $model->load([
                    $model->getFormName() => [
                        'email' => $sessionData['email'] ?? '',
                        'firstname' => $sessionData['firstname'] ?? '',
                        'lastname' => $sessionData['lastname'] ?? '',
                    ],
                ]);
            }
        }

        if ($request->getMethod() === Method::POST) {
            if ($model->load($request->getParsedBody()) && $model->validate()) {
                // Store in session
                $this->session->set(self::SESSION_KEY, [
                    'email' => $model->getEmail(),
                    'password' => $model->getPassword(),
                    'firstname' => $model->getFirstname(),
                    'lastname' => $model->getLastname(),
                ]);

                return $this->redirect('dboard.onboarding.step2');
            }
        }

        return $this->render('Onboarding/step1', [
            'model' => $model,
            'urlGenerator' => $this->urlGenerator,
        ]);
    }
}
