<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Helpers;

use DateTimeImmutable;
use DateTimeInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * Elapsed time since a date, as a translated label (3 days ago, Just now).
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class RelativeTimeHelper
{
    /**
     * Returns the elapsed time since the date in its largest unit, empty when there is no date.
     */
    public static function format(?DateTimeInterface $date, TranslatorInterface $translator): string
    {
        $relativeTime = '';
        if ($date !== null) {
            $elapsed = (new DateTimeImmutable())->diff($date);
            if ($elapsed->y > 0) {
                $relativeTime = $translator->translate('{count, plural, one{# year ago} other{# years ago}}', [
                    'count' => $elapsed->y,
                ], category: 'dboard-modules');
            } elseif ($elapsed->m > 0) {
                $relativeTime = $translator->translate('{count, plural, one{# month ago} other{# months ago}}', [
                    'count' => $elapsed->m,
                ], category: 'dboard-modules');
            } elseif ($elapsed->d > 0) {
                $relativeTime = $translator->translate('{count, plural, one{# day ago} other{# days ago}}', [
                    'count' => $elapsed->d,
                ], category: 'dboard-modules');
            } elseif ($elapsed->h > 0) {
                $relativeTime = $translator->translate('{count, plural, one{# hour ago} other{# hours ago}}', [
                    'count' => $elapsed->h,
                ], category: 'dboard-modules');
            } elseif ($elapsed->i > 0) {
                $relativeTime = $translator->translate('{count, plural, one{# minute ago} other{# minutes ago}}', [
                    'count' => $elapsed->i,
                ], category: 'dboard-modules');
            } else {
                $relativeTime = $translator->translate('Just now', category: 'dboard-modules');
            }
        }
        return $relativeTime;
    }
}
