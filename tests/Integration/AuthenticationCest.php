<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Tests\Integration;

use Blackcube\Dboard\Models\Administrator;
use Blackcube\Dboard\Models\Forms\LoginForm;
use Blackcube\Dboard\Services\Client;
use Blackcube\Dboard\Services\RefreshToken;
use Blackcube\Dboard\Services\ScopeProvider;
use Blackcube\Dboard\Tests\Support\DatabaseCestTrait;
use Blackcube\Dboard\Tests\Support\IntegrationTester;
use Blackcube\Oauth2\Jwt\CypherKey;
use Blackcube\Oauth2\PopulationConfig;
use Blackcube\Oauth2\Server\Oauth2ServerFactory;
use Blackcube\Oauth2\Storage\Oauth2Storage;
use OAuth2\Request as Oauth2Request;
use OAuth2\Response as Oauth2Response;

/**
 * The sign-in screen against the database: what the login form accepts, and
 * what the password grant answers, the grant being built exactly as
 * Handlers\Authentication\Login builds it.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class AuthenticationCest
{
    use DatabaseCestTrait;

    private const EMAIL = 'philippe@blackcube.io';
    private const PASSWORD = 'p';

    public function testLoginFormRefusesAnEmptySubmission(IntegrationTester $I): void
    {
        $I->wantTo('verify the login form requires an e-mail and a password');

        $form = new LoginForm();
        $form->setScenario('login');
        $form->load([
            'LoginForm' => [
                'email' => '',
                'password' => '',
            ],
        ]);

        $I->assertFalse($form->validate());
        $I->assertFalse($form->getValidationResult()->isPropertyValid('email'));
        $I->assertFalse($form->getValidationResult()->isPropertyValid('password'));
    }

    public function testLoginFormRefusesAMalformedEmail(IntegrationTester $I): void
    {
        $I->wantTo('verify the login form requires a well-formed e-mail');

        $form = new LoginForm();
        $form->setScenario('login');
        $form->load([
            'LoginForm' => [
                'email' => 'philippe',
                'password' => self::PASSWORD,
            ],
        ]);

        $I->assertFalse($form->validate());
        $I->assertFalse($form->getValidationResult()->isPropertyValid('email'));
        $I->assertTrue($form->getValidationResult()->isPropertyValid('password'));
    }

    public function testLoginFormAcceptsCredentialsAndRememberMe(IntegrationTester $I): void
    {
        $I->wantTo('verify the login form hydrates the credentials and the remember me flag');

        $form = new LoginForm();
        $form->setScenario('login');
        $form->load([
            'LoginForm' => [
                'email' => self::EMAIL,
                'password' => self::PASSWORD,
                'rememberMe' => '1',
            ],
        ]);

        $I->assertTrue($form->validate());
        $I->assertSame(self::EMAIL, $form->getEmail());
        $I->assertSame(self::PASSWORD, $form->getPassword());
        $I->assertTrue($form->isRememberMe());
    }

    public function testPasswordGrantIssuesTokensForValidCredentials(IntegrationTester $I): void
    {
        $I->wantTo('verify the password grant answers with an access token and stores a refresh token');

        $this->createAdministrator(true);
        $tokenResponse = $this->requestToken(self::EMAIL, self::PASSWORD);
        $tokenData = json_decode($tokenResponse->getResponseBody(), true);

        $I->assertSame(200, $tokenResponse->getStatusCode());
        $I->assertArrayHasKey('access_token', $tokenData);
        $I->assertArrayHasKey('refresh_token', $tokenData);
        $I->assertSame(1, (int) RefreshToken::query()->count());
    }

    public function testPasswordGrantRefusesAWrongPassword(IntegrationTester $I): void
    {
        $I->wantTo('verify the password grant refuses a wrong password');

        $this->createAdministrator(true);
        $tokenResponse = $this->requestToken(self::EMAIL, 'wrong-password');
        $tokenData = json_decode($tokenResponse->getResponseBody(), true);

        $I->assertSame(401, $tokenResponse->getStatusCode());
        $I->assertSame('invalid_grant', $tokenData['error']);
        $I->assertArrayNotHasKey('access_token', $tokenData);
        $I->assertSame(0, (int) RefreshToken::query()->count());
    }

    public function testPasswordGrantRefusesAnUnknownEmail(IntegrationTester $I): void
    {
        $I->wantTo('verify the password grant refuses an e-mail nobody holds');

        $this->createAdministrator(true);
        $tokenResponse = $this->requestToken('nobody@blackcube.io', self::PASSWORD);
        $tokenData = json_decode($tokenResponse->getResponseBody(), true);

        $I->assertSame(401, $tokenResponse->getStatusCode());
        $I->assertSame('invalid_grant', $tokenData['error']);
        $I->assertArrayNotHasKey('access_token', $tokenData);
    }

    public function testPasswordGrantRefusesAnInactiveAdministrator(IntegrationTester $I): void
    {
        $I->wantTo('verify the password grant refuses a deactivated administrator, even with the right password');

        $this->createAdministrator(false);
        $tokenResponse = $this->requestToken(self::EMAIL, self::PASSWORD);
        $tokenData = json_decode($tokenResponse->getResponseBody(), true);

        $I->assertSame(401, $tokenResponse->getStatusCode());
        $I->assertSame('invalid_grant', $tokenData['error']);
        $I->assertArrayNotHasKey('access_token', $tokenData);
        $I->assertSame(0, (int) RefreshToken::query()->count());
    }

    private function createAdministrator(bool $active): Administrator
    {
        $administrator = new Administrator();
        $administrator->setFirstname('Philippe');
        $administrator->setLastname('Gaultier');
        $administrator->setEmail(self::EMAIL);
        $administrator->setPassword(self::PASSWORD);
        $administrator->setActive($active);
        $administrator->save();

        return $administrator;
    }

    /**
     * The password grant as Handlers\Authentication\Login plays it: the dboard
     * population, its public client, the storage over the administrators and
     * the refresh tokens, and the signing key. The key pair is built on first
     * use under tests/_output, where nothing is committed.
     */
    private function requestToken(string $email, string $password): Oauth2Response
    {
        $keysPath = dirname(__DIR__) . '/_output/oauth2';
        $cypherKey = new CypherKey(
            id: 'dboard-tests',
            rsPublicKey: $keysPath . '/public.pem',
            rsPrivateKey: $keysPath . '/private.pem',
        );

        $config = new PopulationConfig(
            name: 'dboard',
            issuer: $cypherKey->getId(),
            audience: 'dboard',
            userQueryClass: Administrator::class,
            clientQueryClass: Client::class,
            refreshTokenQueryClass: RefreshToken::class,
            cypherKeyQueryClass: $cypherKey::class,
            accessTokenTtl: 3600,
            refreshTokenTtl: 86400,
            allowedGrants: [
                'password',
                'refresh_token',
            ],
        );

        $storage = new Oauth2Storage(
            userClass: $config->userQueryClass,
            clientClass: $config->clientQueryClass,
            refreshTokenClass: $config->refreshTokenQueryClass,
            scopeProvider: new ScopeProvider(),
            cypherKeyClass: $config->cypherKeyQueryClass,
        );

        $server = Oauth2ServerFactory::create($storage, $config);

        $tokenRequest = new Oauth2Request(
            query: [],
            request: [
                'grant_type' => 'password',
                'username' => $email,
                'password' => $password,
                'client_id' => 'dboard',
            ],
            attributes: [],
            cookies: [],
            files: [],
            server: [
                'REQUEST_METHOD' => 'POST',
            ]
        );

        $tokenResponse = new Oauth2Response();
        $server->handleTokenRequest($tokenRequest, $tokenResponse);

        return $tokenResponse;
    }
}
