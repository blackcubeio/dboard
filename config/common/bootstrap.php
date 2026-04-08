<?php

declare(strict_types=1);

/**
 * bootstrap.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Yiisoft\Aliases\Aliases;

return [
    static function (\Psr\Container\ContainerInterface $container): void {
        if ($container->has(Aliases::class)) {
            $container->get(Aliases::class)->set('@dboard', dirname(__DIR__, 2) . '/src');
        }
    },
];
