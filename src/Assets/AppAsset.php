<?php

declare(strict_types=1);

/**
 * AppAsset.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Assets;

use Blackcube\Assets\WebpackAssetBundle;

final class AppAsset extends WebpackAssetBundle
{
    public ?string $basePath = '@assets';
    public ?string $baseUrl = '@assetsUrl';
    public ?string $sourcePath = '@dboard/Assets/App/dist-webpack';
}
