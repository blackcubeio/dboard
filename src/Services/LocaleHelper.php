<?php

declare(strict_types=1);

/**
 * LocaleHelper.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Services;

use Locale;

final class LocaleHelper
{
    private const LOCALES = ['de', 'en', 'es', 'fr', 'it', 'pt'];

    /**
     * Returns locale options for select widgets.
     * Keys are locale codes, values are display names in their own language.
     *
     * @return array<string, string>
     */
    public static function getOptions(): array
    {
        $options = [];
        foreach (self::LOCALES as $locale) {
            $options[$locale] = ucfirst(Locale::getDisplayName($locale, $locale));
        }
        return $options;
    }

    /**
     * Returns the list of supported locale codes.
     *
     * @return string[]
     */
    public static function getLocales(): array
    {
        return self::LOCALES;
    }
}
