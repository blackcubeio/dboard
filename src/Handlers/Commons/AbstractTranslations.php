<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Handler\OutputType;
use Blackcube\Dboard\Models\Forms\TranslationForm;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\AjaxifyAction;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Enums\UiLevel;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * Abstract translations action for managing entity translations.
 * Handles GET (display), POST (link), DELETE (unlink) operations.
 * Inherits from AbstractAjaxHandler and uses ActionModel configuration.
 *
 * Pipeline: setupAction() -> setupMethod() -> try { handleMethod() } catch -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractTranslations extends AbstractAjaxHandler
{
    /**
     * Returns the model class name.
     *
     * @return string Fully qualified class name of the ActiveRecord model
     */
    abstract protected function getModelClass(): string;

    /**
     * Returns the entity name for display messages.
     *
     * @return string The entity name (e.g., 'content')
     */
    abstract protected function getEntityName(): string;

    /**
     * Returns the DOM element ID for the translations list container.
     *
     * @return string The DOM element ID
     */
    abstract protected function getTranslationsListId(): string;

    /**
     * @var TranslationForm|null The form model for linking translations
     */
    protected ?TranslationForm $linkFormModel = null;

    /**
     * @var array<int, TranslationForm> The form models for existing translations
     */
    protected array $translationForms = [];

    /**
     * @var array The orphan models that can be linked
     */
    protected array $orphans = [];

    /**
     * @var bool Whether a translation was successfully linked
     */
    protected bool $linked = false;

    /**
     * @var bool Whether a translation was successfully unlinked
     */
    protected bool $unlinked = false;

    /**
     * {@inheritdoc}
     */
    protected function getActionModels(): array
    {
        return [
            'main' => new ActionModel(
                modelClass: $this->getModelClass(),
                formModelClass: null,
                isMain: true,
            ),
        ];
    }

    /**
     * Sets up the action and prepares translation forms.
     *
     * @return ResponseInterface|null Response if setup failed, null if successful
     */
    protected function setupAction(): ?ResponseInterface
    {
        $setupResponse = parent::setupAction();
        if ($setupResponse === null) {
            $model = $this->models['main'];

            $this->linkFormModel = new TranslationForm(translator: $this->translator);

            $this->translationForms = [];
            /** @var Translation $translation */
            foreach ($model->getTranslationsQuery()->each() as $translation) {
                $form = new TranslationForm(translator: $this->translator);
                $form->setTargetId($translation->getId());
                $form->setTargetName($translation->getName());
                $form->setTargetLanguageId($translation->getLanguageId());
                $this->translationForms[] = $form;
            }

            $usedLanguages = [];
            foreach ($this->translationForms as $translationForm) {
                $usedLanguages[] = $translationForm->getTargetLanguageId();
            }
            $usedLanguages[] = $model->getLanguageId();

            $modelClass = $this->getModelClass();
            $this->orphans = [];
            $orphansQuery = $modelClass::query()
                ->andWhere([
                    'translationGroupId' => null,
                ])
                ->andWhere([
                    'not in',
                    'languageId',
                    $usedLanguages,
                ]);
            /** @var Orphan $orphan */
            foreach ($orphansQuery->each() as $orphan) {
                $this->orphans[] = $orphan;
            }
        }

        return $setupResponse;
    }

    /**
     * {@inheritdoc}
     */
    protected function setupMethod(): void
    {
        if ($this->request->getMethod() === Method::POST) {
            $this->linkFormModel->setScenario('link');
            $this->linkFormModel->load($this->getBodyParams());
        }
    }

    /**
     * Hook called before link/unlink.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function beforeSave(bool $inTransaction): void
    {
    }

    /**
     * Hook called after link/unlink.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function afterSave(bool $inTransaction): void
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function handleMethod(): void
    {
        $method = $this->request->getMethod();
        $model = $this->models['main'];

        if ($method === Method::POST) {
            if ($this->linkFormModel->validate() === true) {
                $this->beforeSave(false);
                $transaction = $model->db()->beginTransaction();
                try {
                    $this->beforeSave(true);
                    $model->linkTranslation($this->linkFormModel->getTargetId());
                    $this->afterSave(true);
                    $transaction->commit();
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
                $this->afterSave(false);

                $this->linked = true;
            }
        } elseif ($method === Method::DELETE) {
            $unlinkForm = new TranslationForm(translator: $this->translator);
            $unlinkForm->setScenario('unlink');
            $unlinkForm->load($this->getBodyParams());

            if ($unlinkForm->validate() === false) {
                throw new \RuntimeException('Invalid parameters.');
            }

            $this->beforeSave(false);
            $transaction = $model->db()->beginTransaction();
            try {
                $this->beforeSave(true);
                $model->unlinkTranslation($unlinkForm->getTargetId());
                $this->afterSave(true);
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
            $this->afterSave(false);

            $this->unlinked = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $model = $this->models['main'];
        $routeParams = $this->extractPrimaryKeysFromModel();

        if ($this->linked === true) {
            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Keep),
                    ...AureliaCommunication::ajaxify(
                        $this->getTranslationsListId(),
                        $this->urlGenerator->generate($this->currentRoute->getName(), $routeParams),
                        AjaxifyAction::Refresh
                    ),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-common'),
                        $this->translator->translate('Translation linked.', category: 'dboard-common'),
                        UiLevel::Success
                    ),
                ],
            ];
        } elseif ($this->unlinked === true) {
            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Keep),
                    ...AureliaCommunication::ajaxify(
                        $this->getTranslationsListId(),
                        $this->urlGenerator->generate($this->currentRoute->getName(), $routeParams),
                        AjaxifyAction::Refresh
                    ),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-common'),
                        $this->translator->translate('Translation unlinked.', category: 'dboard-common'),
                        UiLevel::Success
                    ),
                ],
            ];
        } elseif ($this->isAjaxify() === true) {
            $outputData = [
                'type' => OutputType::Partial->value,
                'view' => 'Commons/_translations-list-content',
                'data' => [
                    'model' => $model,
                    'translationForms' => $this->translationForms,
                    'orphans' => $this->orphans,
                    'linkFormModel' => $this->linkFormModel,
                    'urlGenerator' => $this->urlGenerator,
                    'formAction' => $this->urlGenerator->generate($this->currentRoute->getName(), $routeParams),
                    'translationsListId' => $this->getTranslationsListId(),
                ],
            ];
        } else {
            $content = (string) $this->renderPartial('Commons/_translations-content', [
                'model' => $model,
                'translationForms' => $this->translationForms,
                'orphans' => $this->orphans,
                'linkFormModel' => $this->linkFormModel,
                'urlGenerator' => $this->urlGenerator,
                'formAction' => $this->urlGenerator->generate($this->currentRoute->getName(), $routeParams),
                'translationsListId' => $this->getTranslationsListId(),
            ])->getBody();

            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Keep),
                    ...AureliaCommunication::dialogContent(Bleet::dialogFrame(DialogType::Drawer)
                        ->title($this->translator->translate('Translations', category: 'dboard-common'))
                        ->primary()
                        ->content($content)
                        ->render()),
                ],
            ];
        }

        return $outputData;
    }
}