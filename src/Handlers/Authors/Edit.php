<?php

declare(strict_types=1);

/**
 * Edit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Authors;

use Blackcube\Dcore\Models\Author;
use Blackcube\Dboard\Handlers\Commons\AbstractEdit;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Dboard\Models\Forms\AuthorForm;
use Yiisoft\Router\CurrentRoute;

/**
 * Author edit action.
 */
final class Edit extends AbstractEdit
{
    protected function getModelClass(): string { return Author::class; }
    protected function getFormModelClass(): string { return AuthorForm::class; }
    protected function getEntityName(): string { return 'author'; }
    protected function getViewPrefix(): string { return 'Authors'; }
    protected function getListRoute(): string { return 'dboard.authors'; }

    protected function prepareOutputData(): array
    {
        $output = parent::prepareOutputData();
        if (($output['type'] ?? null) === OutputType::Render->value) {
            $fileRoutePrefix = $this->getFileRoutePrefix();
            $output['data']['fileEndpoints'] = [
                'upload' => $this->urlGenerator->generate($fileRoutePrefix . '.files.upload'),
                'preview' => $this->urlGenerator->generate($fileRoutePrefix . '.files.preview'),
                'delete' => $this->urlGenerator->generate($fileRoutePrefix . '.files.delete'),
            ];
        }
        return $output;
    }
}
