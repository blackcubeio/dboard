<?php

declare(strict_types=1);

/**
 * Dboard.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard;

use Yiisoft\Definitions\DynamicReference;
use Yiisoft\Translator\CategorySource;
use Yiisoft\Translator\Message\Db\MessageSource;

final class Dboard
{
    private const CATEGORIES = [
        'dboard-common',
        'dboard-content',
        'dboard-modules',
        'dboard-onboarding',
        'dboard-builtin',
    ];

    public static function getI18nSources(): array
    {
        $sources = [];
        foreach (self::CATEGORIES as $category) {
            $sources["translation.$category"] = [
                'class' => CategorySource::class,
                '__construct()' => [
                    'name' => $category,
                    'reader' => DynamicReference::to(MessageSource::class),
                ],
                'tags' => ['translation.categorySource'],
            ];
        }
        return $sources;
    }
}
