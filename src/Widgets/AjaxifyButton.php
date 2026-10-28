<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Widgets;

use Blackcube\Bleet\Field\Button;
use Yiisoft\Html\Html;

/**
 * A Bleet button that is its own ajaxify trigger: the hidden inputs of its
 * payload are rendered inside it, disabled, where the trigger attribute reads
 * them. It takes its place in a button group like any other button.
 *
 * Usage:
 *   Widgets::ajaxifyButton()
 *       ->icon('chevron-up')
 *       ->hidden('BlocForm[blocId]', $blocId)
 *       ->hidden('BlocForm[mode]', ReorderMode::MoveUp->value)
 *       ->addAttributes(Bleet::ajaxify()->mode(AjaxifyTriggerMode::PublishOnly)->target($listId)->trigger())
 *       ->render()
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class AjaxifyButton extends Button
{
    /** @var array<string, string> the hidden inputs of the payload, value by name */
    protected array $hiddenInputs = [];

    /**
     * A hidden input of the payload, disabled until the trigger fires.
     */
    public function hidden(string $name, string $value): static
    {
        $new = clone $this;
        $new->hiddenInputs[$name] = $value;
        return $new;
    }

    /**
     * The badge of the button, then its payload.
     */
    protected function renderAfterText(): string
    {
        $html = parent::renderAfterText();

        foreach ($this->hiddenInputs as $name => $value) {
            $html .= Html::input('hidden', $name, $value, [
                'disabled' => true,
            ])->render();
        }

        return $html;
    }
}
