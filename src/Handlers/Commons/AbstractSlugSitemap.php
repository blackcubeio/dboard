<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Handlers\Commons;

use Blackcube\Dcore\Models\Sitemap;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dcore\Models\Xeo;
use Blackcube\Dboard\Components\ActionModel;
use Blackcube\Handler\OutputType;
use Blackcube\Dboard\Models\Forms\SitemapForm;
use Blackcube\Dboard\Models\Forms\SlugForm;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\DialogType;
use Blackcube\Bleet\Enums\UiLevel;
use Blackcube\Bleet\Helper\AureliaCommunication;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;

/**
 * Abstract action for managing Slug and Sitemap.
 * Inherits from AbstractAjaxHandler and uses ActionModel configuration.
 *
 * Pipeline: setupAction() -> setupMethod() -> try { handleMethod() } catch -> prepareOutputData() -> output()
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractSlugSitemap extends AbstractAjaxHandler
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
     * @return string The entity name (e.g., 'content', 'tag')
     */
    abstract protected function getEntityName(): string;

    /**
     * Returns the route name for slug generation.
     *
     * @return string The route name
     */
    abstract protected function getSlugGeneratorRoute(): string;

    /**
     * @var Slug|null The slug model
     */
    protected ?Slug $slug = null;

    /**
     * @var Sitemap|null The sitemap model
     */
    protected ?Sitemap $sitemap = null;

    /**
     * @var SlugForm|null The slug form model
     */
    protected ?SlugForm $slugForm = null;

    /**
     * @var SitemapForm|null The sitemap form model
     */
    protected ?SitemapForm $sitemapForm = null;

    /**
     * @var bool Whether the save operation was successful
     */
    protected bool $saved = false;

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
     * Sets up the action and prepares slug/sitemap forms.
     *
     * @return ResponseInterface|null Response if setup failed, null if successful
     */
    protected function setupAction(): ?ResponseInterface
    {
        $setupResponse = parent::setupAction();
        if ($setupResponse === null) {
            $model = $this->models['main'];
            $entityName = $this->getEntityName();

            if ($model->getTypeId() === null) {
                throw new \RuntimeException(ucfirst($entityName) . ' must have a type to manage the slug.');
            }

            $this->slug = $model->getSlugQuery()->one();
            if ($this->slug !== null) {
                $this->slugForm = SlugForm::createFromModel($this->slug, $this->translator);
                $this->slugForm->setScenario('edit');
                $this->sitemap = $this->slug->getSitemapQuery()->one();
            } else {
                $this->slug = new Slug();
                $this->slugForm = new SlugForm(translator: $this->translator);
                $this->slugForm->setScenario('create');
            }

            if ($this->sitemap !== null) {
                $this->sitemapForm = SitemapForm::createFromModel($this->sitemap, $this->translator);
                $this->sitemapForm->setScenario('edit');
            } else {
                $this->sitemap = new Sitemap();
                $this->sitemapForm = new SitemapForm(translator: $this->translator);
                $this->sitemapForm->setScenario('create');
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
            $bodyParams = $this->getBodyParams() ?? [];
            $this->slugForm->load($bodyParams);
            $this->sitemapForm->load($bodyParams);
        }
    }

    /**
     * Hook called before save.
     *
     * @param bool $inTransaction Whether we are inside the transaction
     */
    protected function beforeSave(bool $inTransaction): void
    {
    }

    /**
     * Hook called after save.
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
        if ($this->request->getMethod() === Method::POST) {
            $slugValid = $this->slugForm->validate();
            $sitemapValid = $this->sitemapForm->validate();

            if ($slugValid === true && $sitemapValid === true) {
                $model = $this->models['main'];

                $this->beforeSave(false);
                $transaction = $this->slug->db()->beginTransaction();
                try {
                    $this->beforeSave(true);

                    $this->slugForm->populateModel($this->slug);
                    $this->slug->save();

                    if ($model->getSlugId() !== $this->slug->getId()) {
                        $model->setSlugId($this->slug->getId());
                        $model->save();
                    }

                    $xeo = $this->slug->getXeoQuery()->one();
                    if ($xeo === null) {
                        $xeo = new Xeo();
                        $xeo->setSlugId($this->slug->getId());
                        $xeo->save();
                    }

                    $this->sitemap->setSlugId($this->slug->getId());
                    $this->sitemapForm->populateModel($this->sitemap);
                    $this->sitemap->save();

                    $this->afterSave(true);
                    $transaction->commit();
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
                $this->afterSave(false);

                $this->saved = true;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareOutputData(): array
    {
        $model = $this->models['main'];
        $routeParams = $this->extractPrimaryKeysFromModel();

        if ($this->saved === true) {
            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Close),
                    ...AureliaCommunication::toast(
                        $this->translator->translate('Success', category: 'dboard-common'),
                        $this->translator->translate('Slug and sitemap saved.', category: 'dboard-common'),
                        UiLevel::Success
                    ),
                ],
            ];
        } else {
            $content = (string) $this->renderPartial('Commons/_slug-sitemap-content', [
                'model' => $model,
                'slugForm' => $this->slugForm,
                'sitemapForm' => $this->sitemapForm,
                'urlGenerator' => $this->urlGenerator,
                'formAction' => $this->urlGenerator->generate($this->currentRoute->getName(), $routeParams),
                'slugGeneratorUrl' => $this->urlGenerator->generate(
                    $this->getSlugGeneratorRoute(),
                    $routeParams
                ),
            ])->getBody();

            $outputData = [
                'type' => OutputType::Json->value,
                'data' => [
                    ...AureliaCommunication::dialog(DialogAction::Keep),
                    ...AureliaCommunication::dialogContent(Bleet::dialogFrame(DialogType::Drawer)
                        ->title('Slug / Sitemap')
                        ->primary()
                        ->content($content)
                        ->render()),
                ],
            ];
        }

        return $outputData;
    }
}