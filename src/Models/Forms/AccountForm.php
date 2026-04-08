<?php

declare(strict_types=1);

/**
 * AccountForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Result;

/**
 * Account form model.
 * Used by Account/Index for admin self-editing.
 * Same as AdministratorForm but without 'active' field.
 */
final class AccountForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-modules';

    protected string $email = '';
    protected string $firstname = '';
    protected string $lastname = '';
    protected ?string $locale = null;
    protected string $password = '';
    protected string $checkPassword = '';

    #[Bridge]
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
    #[Bridge]
    public function getEmail(): string
    {
        return $this->email;
    }
    #[Bridge]
    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }
    #[Bridge]
    public function getFirstname(): string
    {
        return $this->firstname;
    }
    #[Bridge]
    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }
    #[Bridge]
    public function getLastname(): string
    {
        return $this->lastname;
    }
    #[Bridge]
    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }
    #[Bridge]
    public function getLocale(): ?string
    {
        return $this->locale;
    }
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
    #[Bridge(setter: 'setPassword', property: false)]
    public function getPassword(): string
    {
        return $this->password;
    }
    public function setCheckPassword(string $checkPassword): void
    {
        $this->checkPassword = $checkPassword;
    }
    public function getCheckPassword(): string
    {
        return $this->checkPassword;
    }

    public function load(mixed $data, ?string $scope = null): bool
    {
        $result = parent::load($data, $scope);
        if ($this->locale === '') {
            $this->locale = null;
        }
        return $result;
    }

    public function scenarios(): array
    {
        return [
            'edit' => ['email', 'firstname', 'lastname', 'locale', 'password', 'checkPassword'],
        ];
    }

    public function rules(): array
    {
        return [
            'email' => [
                new Required(),
                new Email(),
                new Length(max: 255),
            ],
            'firstname' => [
                new Required(),
                new Length(max: 255),
            ],
            'lastname' => [
                new Required(),
                new Length(max: 255),
            ],
            'locale' => [
                new Length(max: 16),
            ],
            'password' => [
                new Callback(
                    callback: function (mixed $value): Result {
                        $result = new Result();
                        if (empty($value) && !empty($this->checkPassword)) {
                            $result->addError('Password is required.');
                        }
                        if (!empty($value) && strlen($value) < 8) {
                            $result->addError('Password must be at least 8 characters.');
                        }
                        return $result;
                    }
                ),
            ],
            'checkPassword' => [
                new Callback(
                    callback: function (mixed $value): Result {
                        $result = new Result();
                        if (empty($value) && !empty($this->password)) {
                            $result->addError('Confirmation is required.');
                        }
                        if (!empty($this->password) && $value !== $this->password) {
                            $result->addError('Passwords do not match.');
                        }
                        return $result;
                    }
                ),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'email' => 'Email',
            'firstname' => 'First name',
            'lastname' => 'Last name',
            'locale' => 'Language',
            'password' => 'Password',
            'checkPassword' => 'Confirm password',
        ];
    }

    protected function getRawHints(): array
    {
        return [
            'locale' => 'Interface language',
            'password' => 'Minimum 8 characters',
            'checkPassword' => 'Must match the password',
        ];
    }
}
