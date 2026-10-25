<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Field\Elastic as BleetElastic;
use Blackcube\BridgeForm\Field\Upload as BaseUpload;
use Yiisoft\FormModel\FormModelInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * The Bleet field derived from the schema, with the dboard texts: the uploads carry
 * the translated dropzone, browse and error texts.
 *
 * Usage:
 *   Widgets::elastic($model, $attribute, $elasticOptions)->translator($translator)->render()
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Elastic extends BleetElastic
{
    private ?TranslatorInterface $translator = null;

    public function translator(TranslatorInterface $translator): self
    {
        $new = clone $this;
        $new->translator = $translator;
        return $new;
    }

    protected function makeUpload(FormModelInterface $model, string $property): BaseUpload
    {
        $upload = Bleet::upload($model, $property)->secondary();
        if ($this->translator !== null) {
            $upload = $upload
                ->dropzoneText($this->translator->translate('Drop file here', category: 'dboard-common'))
                ->browseSeparator($this->translator->translate('or', category: 'dboard-common'))
                ->browseLabel($this->translator->translate('browse', category: 'dboard-common'))
                ->typeErrorText($this->translator->translate('The file "{name}" is not an accepted type ({types}).', [
                    'name' => '{name}',
                    'types' => '{types}',
                ], 'dboard-common'))
                ->responseErrorText($this->translator->translate('Invalid server response.', category: 'dboard-common'))
                ->uploadErrorText($this->translator->translate('Failed to upload "{name}".', [
                    'name' => '{name}',
                ], 'dboard-common'))
                ->errorTitle($this->translator->translate('Error', category: 'dboard-common'));
        }
        return $upload;
    }
}
