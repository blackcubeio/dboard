<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeFormModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\Each;
use Yiisoft\Validator\Rule\In;
use Yiisoft\Validator\Rule\Required;

/**
 * Consent form of the OAuth2 authorization screen: the decision of the
 * administrator and the permissions granted to the client, bounded by the
 * permissions offered, those the population publishes and the administrator
 * holds.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ConsentForm extends BridgeFormModel
{
    public const DECISION_ALLOW = 'allow';
    public const DECISION_DENY = 'deny';

    protected ?string $translateCategory = 'dboard-modules';

    protected string $decision = '';
    protected array $scopes = [];

    /**
     * @var string[]
     */
    private array $offered = [];

    public function load(mixed $submittedValues, ?string $scope = null): bool
    {
        $this->scopes = [];
        return parent::load($submittedValues, $scope);
    }

    #[Bridge]
    public function setDecision(string $decision): void
    {
        $this->decision = $decision;
    }

    #[Bridge]
    public function getDecision(): string
    {
        return $this->decision;
    }

    /**
     * @param string[] $scopes
     */
    #[Bridge]
    public function setScopes(array $scopes): void
    {
        $this->scopes = $scopes;
    }

    /**
     * @return string[]
     */
    #[Bridge]
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @param string[] $offered
     */
    public function setOffered(array $offered): void
    {
        $this->offered = $offered;
    }

    public function isAllowed(): bool
    {
        return $this->decision === self::DECISION_ALLOW && empty($this->scopes) === false;
    }

    public function scenarios(): array
    {
        return [
            'default' => [
                'decision',
                'scopes',
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'decision' => [
                new Required(),
                new In([
                    self::DECISION_ALLOW,
                    self::DECISION_DENY,
                ]),
            ],
            'scopes' => [
                new Each([
                    new In($this->offered),
                ]),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'decision' => 'Decision',
            'scopes' => 'Rights',
        ];
    }
}
