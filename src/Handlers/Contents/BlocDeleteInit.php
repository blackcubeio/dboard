<?php

declare(strict_types=1);

/**
 * BlocDeleteInit.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Contents;

use Blackcube\Dcore\Models\Bloc;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\ContentBloc;
use Blackcube\Dboard\Handlers\Commons\AbstractAjaxHandler;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Dboard\Enums\OutputType;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Router\CurrentRoute;

/**
 * Content bloc delete init action (GET).
 * Displays delete confirmation modal for a bloc.
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class BlocDeleteInit extends AbstractAjaxHandler
{
    protected ?ActiveRecord $pivot = null;
    protected ?Bloc $bloc = null;

    protected function getActionModels(): array
    {
        return [
            'main' => new ActionModel(
                modelClass: Content::class,
                formModelClass: null,
                isMain: true,
            ),
        ];
    }

    protected function setupAction(): ?ResponseInterface
    {
        $response = parent::setupAction();
        if ($response !== null) {
            return $response;
        }

        $blocId = $this->currentRoute->getArgument('blocId');
        $this->bloc = Bloc::query()
            ->andWhere(['id' => $blocId])
            ->one();

        if ($this->bloc === null) {
            throw new \RuntimeException('Block not found.');
        }

        $model = $this->models['main'];
        $this->pivot = ContentBloc::query()
            ->andWhere([
                'contentId' => $model->getId(),
                'blocId' => $this->bloc->getId()
            ])
            ->one();

        if ($this->pivot === null) {
            throw new \RuntimeException('This block does not belong to this content.');
        }

        return null;
    }

    protected function prepareOutputData(): array
    {
        $model = $this->models['main'];

        $elasticSchema = $this->bloc->elasticSchema;
        $blocName = $elasticSchema?->name ?? 'Bloc #' . $this->bloc->getId();
        $message = $this->translator->translate('Block "{name}" will be permanently deleted.', ['name' => $blocName], 'dboard-content');

        $header = (string) $this->renderPartial('Commons/_modal-header', [
            'title' => $this->translator->translate('Delete block', category: 'dboard-content'),
            'uiColor' => UiColor::Danger,
        ])->getBody();

        $content = (string) $this->renderPartial('Commons/_delete-content', [
            'message' => $message,
            'formAction' => $this->urlGenerator->generate(
                $this->currentRoute->getName(),
                ['id' => $model->getId(), 'blocId' => $this->bloc->getId()]
            ),
        ])->getBody();

        return [
            'type' => OutputType::Json->value,
            'data' => [
                ...AureliaCommunication::dialog(DialogAction::Keep),
                ...AureliaCommunication::dialogContent($header, $content, UiColor::Danger),
            ],
        ];
    }
}
