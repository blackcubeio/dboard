<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
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
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Translations extends AbstractTranslations
{
    protected function getModelClass(): string { return Tag::class; }
    protected function getEntityName(): string { return 'tag'; }
    protected function getTranslationsListId(): string { return 'tag-translations'; }

    protected function setupAction(): ?ResponseInterface
    {
        $setupResponse = parent::setupAction();
        if ($setupResponse === null) {
            $model = $this->models['main'];
            $level = $model->getLevel();
            $sameLevelOrphans = [];
            foreach ($this->orphans as $orphan) {
                if ($orphan->getLevel() === $level) {
                    $sameLevelOrphans[] = $orphan;
                }
            }
            $this->orphans = $sameLevelOrphans;
        }

        return $setupResponse;
    }
}
