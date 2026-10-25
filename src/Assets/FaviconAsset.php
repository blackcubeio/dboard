<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Assets;

use Yiisoft\Assets\AssetBundle;
use Yiisoft\View\WebView;

/**
 * Bundle for static resources (images, icons).
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class FaviconAsset extends AssetBundle
{
    public ?string $basePath = '@assets';
    public ?string $baseUrl = '@assetsUrl';
    public ?string $sourcePath = '@src/Assets/Favicon';
    public array $css = [
        [
            'favicon-96x96.png',
            WebView::POSITION_HEAD,
            'rel' => 'icon',
            'type' => 'image/png',
            'sizes' => '96x96',
        ],
        [
            'apple-touch-icon.png',
            WebView::POSITION_HEAD,
            'rel' => 'apple-touch-icon',
            'sizes' => '180x180',
        ],
        [
            'favicon.ico',
            WebView::POSITION_HEAD,
            'rel' => 'shortcut icon',
        ],
    ];
}
