<?php

declare(strict_types=1);

/**
 * Create.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Slugs;

use Blackcube\Dcore\Models\Slug;
use Blackcube\Dboard\Handlers\Commons\AbstractCreate;
use Blackcube\Dboard\Models\Forms\SlugForm;

/**
 * Slug create action for redirections.
 */
final class Create extends AbstractCreate
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

    protected function getSuccessRoute(): string
    {
        return 'dboard.slugs.edit';
    }
}
