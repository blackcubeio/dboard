<?php

declare(strict_types=1);

/**
 * LoginForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\FormModel\Attribute\Safe;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\Required;

/**
 * Login form model.
 */
final class LoginForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-modules';

    protected string $email = '';
    protected string $password = '';
    protected bool $rememberMe = false;

    public function load(mixed $data, ?string $scope = null): bool
    {
        $this->rememberMe = false;
        return parent::load($data, $scope);
    }

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
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
    #[Bridge]
    public function getPassword(): string
    {
        return $this->password;
    }
    #[Bridge]
    public function setRememberMe(bool $rememberMe): void
    {
        $this->rememberMe = $rememberMe;
    }
    #[Bridge]
    public function isRememberMe(): bool
    {
        return $this->rememberMe;
    }

    public function scenarios(): array
    {
        return [
            'login' => ['email', 'password', 'rememberMe'],
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
            ],
            'rememberMe' => [
                new Safe(),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'email' => 'E-mail',
            'password' => 'Password',
            'rememberMe' => 'Remember me',
        ];
    }

    public function getPropertyPlaceholders(): array
    {
        return [
            'email' => 'identifiant@siteinternet.com',
            'password' => 'Mot de passe',
        ];
    }
}
