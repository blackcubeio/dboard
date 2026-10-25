<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Helpers;

use Locale;

/**
 * Locales available in the admin panel, as a list and as select options.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class LocaleHelper
{
    private const LOCALES = [
        'de',
        'en',
        'es',
        'fr',
        'it',
        'pt',
    ];

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
