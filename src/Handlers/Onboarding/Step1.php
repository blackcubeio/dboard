<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Step1 extends Onboarding
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $stepResponse = null;

        if ($this->session->get(self::SESSION_ONBOARDING) !== true && $this->isOnboardingRequired() === false) {
            $stepResponse = $this->redirect('dboard.login');
        } else {
            $this->session->set(self::SESSION_ONBOARDING, true);

            $model = new OnboardingForm(translator: $this->translator);
            $model->setScenario('register');

            $adminId = $this->session->get(self::SESSION_ADMIN_ID);
            if ($adminId !== null) {
                $existingAdmin = Administrator::query()->andWhere([
                    'id' => $adminId,
                ])->one();
                if ($existingAdmin !== null) {
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
                $sessionData = $this->session->get(self::SESSION_KEY, []);
                if (empty($sessionData) === false) {
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
                if ($model->load($request->getParsedBody()) === true && $model->validate() === true) {
                    $this->session->set(self::SESSION_KEY, [
                        'email' => $model->getEmail(),
                        'password' => $model->getPassword(),
                        'firstname' => $model->getFirstname(),
                        'lastname' => $model->getLastname(),
                    ]);

                    $stepResponse = $this->redirect('dboard.onboarding.step2');
                }
            }

            if ($stepResponse === null) {
                $stepResponse = $this->render('Onboarding/step1', [
                    'model' => $model,
                    'urlGenerator' => $this->urlGenerator,
                ]);
            }
        }

        return $stepResponse;
    }
}
