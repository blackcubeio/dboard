<?php

declare(strict_types=1);

/**
 * Translations.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Tags;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Dboard\Handlers\Commons\AbstractTranslations;
use Psr\Http\Message\ResponseInterface;

/**
 * Tag translations drawer action.
 * Restricts orphan candidates to same tree level.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Translations extends AbstractTranslations
{
    protected function getModelClass(): string { return Tag::class; }
    protected function getEntityName(): string { return 'tag'; }
    protected function getTranslationsListId(): string { return 'tag-translations'; }

    protected function setupAction(): ?ResponseInterface
    {
        $response = parent::setupAction();
        if ($response !== null) {
            return $response;
        }

        // Filter orphans to same level only
        $model = $this->models['main'];
        $level = $model->getLevel();
        $this->orphans = array_values(array_filter(
            $this->orphans,
            fn($orphan) => $orphan->getLevel() === $level
        ));

        return null;
    }
}
