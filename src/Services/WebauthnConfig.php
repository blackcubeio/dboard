<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Psr\Http\Message\ServerRequestInterface;

/**
 * WebAuthn Relying Party configuration.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class WebauthnConfig
{
    public function __construct(
        private ?string $rpId = null,
        private ?string $rpName = null,
        private int $timeout = 60000,
        private int $challengeLength = 32,
    ) {}

    public function getRpId(?ServerRequestInterface $request = null): string
    {
        $rpId = 'localhost';
        if ($this->rpId !== null) {
            $rpId = $this->rpId;
        } elseif ($request !== null) {
            $rpId = $request->getUri()->getHost();
        }

        return $rpId;
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
