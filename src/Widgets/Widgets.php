<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Widgets\Pager;
use Blackcube\Injector\Injector;
use Yiisoft\Data\Paginator\PaginatorInterface;
use Yiisoft\FormModel\FormModelInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * Widgets factory for Dboard admin panel
 *
 * Usage:
 *   Widgets::sidebar()->user($admin)->currentRoute('dboard.dashboard')->render()
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Widgets
{
    private static ?Sidebar $sidebar = null;
    private static ?Preview $preview = null;

    public static function sidebar(): Sidebar
    {
        if (self::$sidebar === null) {
            self::$sidebar = Injector::get(Sidebar::class);
        }
        return self::$sidebar;
    }

    public static function preview(): Preview
    {
        if (self::$preview === null) {
            self::$preview = Injector::get(Preview::class);
        }
        return self::$preview;
    }

    public static function pageHeading(string $title): PageHeading
    {
        return (new PageHeading())->title($title);
    }

    /**
     * A Bleet button that carries the hidden inputs of its own ajaxify trigger.
     */
    public static function ajaxifyButton(string $content = ''): AjaxifyButton
    {
        return AjaxifyButton::widget()->text($content);
    }

    public static function elastic(?FormModelInterface $model = null, ?string $property = null, array $elasticOptions = []): Elastic
    {
        return new Elastic($model, $property, $elasticOptions);
    }

    /**
     * The Bleet pager with its texts from the dboard-common translations.
     */
    public static function pagination(PaginatorInterface $paginator, UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator): Pager
    {
        return Bleet::pagination($paginator, $urlGenerator)
            ->infoText($translator->translate('Showing {begin} to {end} of {total} results', [
                'begin' => '{begin}',
                'end' => '{end}',
                'total' => '{total}',
            ], 'dboard-common'))
            ->previousLabel($translator->translate('Previous', category: 'dboard-common'))
            ->nextLabel($translator->translate('Next', category: 'dboard-common'))
            ->pageLabel($translator->translate('Page {page}', [
                'page' => '{page}',
            ], 'dboard-common'))
            ->emptyText($translator->translate('No results', category: 'dboard-common'));
    }
}
