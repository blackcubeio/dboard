<?php

declare(strict_types=1);

/**
 * Edit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Slugs;

use Blackcube\Dcore\Models\Slug;
use Blackcube\Dboard\Handlers\Commons\AbstractEdit;
use Blackcube\Dboard\Models\Forms\SlugForm;

/**
 * Slug edit action for redirections.
 */
final class Edit extends AbstractEdit
{
    protected function getModelClass(): string
    {
        return Slug::class;
    }

    protected function getFormModelClass(): string
    {
        return SlugForm::class;
    }

    protected function getFormScenario(): string
    {
        return 'redirect';
    }

    protected function getEntityName(): string
    {
        return 'slug';
    }

    protected function getViewPrefix(): string
    {
        return 'Slugs';
    }

    protected function getListRoute(): string
    {
        return 'dboard.slugs';
    }
}
