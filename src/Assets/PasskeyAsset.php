<?php

declare(strict_types=1);

/**
 * PasskeyAsset.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Assets;

use Yiisoft\Assets\AssetBundle;

final class PasskeyAsset extends AssetBundle
{
    public ?string $basePath = '@assets';
    public ?string $baseUrl = '@assetsUrl';
    public ?string $sourcePath = '@dboard/Assets/Passkeys/Img';
}
