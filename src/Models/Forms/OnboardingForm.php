<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Result;

/**
 * Onboarding form model.
 * Standalone form (no AR bridge) — form → AR only via Step3.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class OnboardingForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-onboarding';

    protected string $email = '';
    protected string $password = '';
    protected string $passwordConfirm = '';
    protected string $firstname = '';
    protected string $lastname = '';

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function scenarios(): array
    {
        return [
            'register' => [
                'email',
                'password',
                'passwordConfirm',
                'firstname',
                'lastname',
            ],
            'confirm' => [
                'email',
                'firstname',
                'lastname',
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'email' => [
                new Required(),
                new Email(),
            ],
            'password' => [
                new Required(),
                new Length(min: 8, skipOnEmpty: true),
            ],
            'passwordConfirm' => [
                new Required(),
                new Callback(
                    callback: $this->checkPasswordConfirmation(...),
                    skipOnEmpty: true,
                ),
            ],
            'firstname' => [
                new Required(),
            ],
            'lastname' => [
                new Required(),
            ],
        ];
    }

    public function getPropertyPlaceholders(): array
    {
        return [
            'email' => 'admin@example.com',
            'password' => $this->translator?->translate('Password (min. 8 characters)', category: 'dboard-onboarding') ?? 'Password (min. 8 characters)',
            'passwordConfirm' => $this->translator?->translate('Confirm password', category: 'dboard-onboarding') ?? 'Confirm password',
            'firstname' => $this->translator?->translate('First name', category: 'dboard-onboarding') ?? 'First name',
            'lastname' => $this->translator?->translate('Last name', category: 'dboard-onboarding') ?? 'Last name',
        ];
    }

    protected function checkPasswordConfirmation(mixed $confirmation): Result
    {
        $validationResult = new Result();
        if ($confirmation !== $this->password) {
            $validationResult->addError('Passwords do not match.');
        }
        return $validationResult;
    }

    protected function getRawLabels(): array
    {
        return [
            'email' => 'E-mail',
            'password' => 'Password',
            'passwordConfirm' => 'Password confirmation',
            'firstname' => 'First name',
            'lastname' => 'Last name',
        ];
    }
}
