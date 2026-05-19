<?php

declare(strict_types=1);

/**
 * XeoAuthorForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeModel\Attributes\Bridge;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Required;

/**
 * Xeo author pivot form model.
 */
final class XeoAuthorForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-content';

    // Pivot fields
    protected ?int $authorId = null;
    protected int $order = 0;

    // Display fields (not bridged — set manually from Author model)
    protected string $authorFirstname = '';
    protected string $authorLastname = '';
    protected bool $authorActive = true;

    #[Bridge]
    public function setAuthorId(?int $authorId): void
    {
        $this->authorId = $authorId;
    }

    #[Bridge]
    public function getAuthorId(): ?int
    {
        return $this->authorId;
    }

    #[Bridge]
    public function setOrder(int $order): void
    {
        $this->order = $order;
    }

    #[Bridge]
    public function getOrder(): int
    {
        return $this->order;
    }

    public function setAuthorFirstname(string $authorFirstname): void
    {
        $this->authorFirstname = $authorFirstname;
    }

    public function getAuthorFirstname(): string
    {
        return $this->authorFirstname;
    }

    public function setAuthorLastname(string $authorLastname): void
    {
        $this->authorLastname = $authorLastname;
    }

    public function getAuthorLastname(): string
    {
        return $this->authorLastname;
    }

    public function setAuthorActive(bool $authorActive): void
    {
        $this->authorActive = $authorActive;
    }

    public function isAuthorActive(): bool
    {
        return $this->authorActive;
    }

    public function getAuthorDisplayName(): string
    {
        return trim($this->authorFirstname . ' ' . $this->authorLastname);
    }

    public function scenarios(): array
    {
        return [
            'xeo' => ['authorId', 'order'],
        ];
    }

    public function rules(): array
    {
        return [
            'authorId' => [
                new Required(),
                new Integer(min: 1),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'authorId' => 'Author',
        ];
    }
}
