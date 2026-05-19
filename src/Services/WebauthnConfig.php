<?php

declare(strict_types=1);

/**
 * WebauthnConfig.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Psr\Http\Message\ServerRequestInterface;

/**
 * WebAuthn Relying Party configuration.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class WebauthnConfig
{
    public function __construct(
        private ?string $rpId = null,
        private ?string $rpName = null,
        private int $timeout = 60000,
        private int $challengeLength = 32,
    ) {}

    public function getRpId(?ServerRequestInterface $request = null): string
    {
        if ($this->rpId !== null) {
            return $this->rpId;
        }

        if ($request !== null) {
            return $request->getUri()->getHost();
        }

        return 'localhost';
    }

    public function getRpName(): string
    {
        return $this->rpName ?? 'Blackcube Admin';
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getChallengeLength(): int
    {
        return $this->challengeLength;
    }
}
