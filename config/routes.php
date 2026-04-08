<?php

declare(strict_types=1);

/**
 * routes.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Handlers\Authentication\Login;
use Blackcube\Dboard\Handlers\Authentication\Logout;
use Blackcube\Dboard\Handlers\Dashboard\Index as DashboardIndex;
use Blackcube\Dboard\Handlers\Search\Index as SearchIndex;
use Blackcube\Dboard\Handlers\Account\Index as AccountIndex;
use Blackcube\Dboard\Handlers\Account\Passkeys as AccountPasskeys;
use Blackcube\Dboard\Handlers\Account\PasskeysInit as AccountPasskeysInit;
use Blackcube\Dboard\Handlers\Authentication\Challenges as AuthChallenges;
use Blackcube\Dboard\Handlers\Authentication\Token as AuthToken;
use Blackcube\Dboard\Handlers\Administrators\Create as AdministratorsCreate;
use Blackcube\Dboard\Handlers\Administrators\Delete as AdministratorsDelete;
use Blackcube\Dboard\Handlers\Administrators\Edit as AdministratorsEdit;
use Blackcube\Dboard\Handlers\Administrators\Index as AdministratorsIndex;
use Blackcube\Dboard\Handlers\Administrators\Permissions as AdministratorsPermissions;
use Blackcube\Dboard\Handlers\Administrators\Toggle as AdministratorsToggle;
use Blackcube\Dboard\Handlers\Administrators\ToggleInit as AdministratorsToggleInit;
use Blackcube\Dboard\Handlers\Administrators\DeleteInit as AdministratorsDeleteInit;
use Blackcube\Dboard\Handlers\Languages\Create as LanguagesCreate;
use Blackcube\Dboard\Handlers\Languages\Delete as LanguagesDelete;
use Blackcube\Dboard\Handlers\Languages\DeleteInit as LanguagesDeleteInit;
use Blackcube\Dboard\Handlers\Languages\Edit as LanguagesEdit;
use Blackcube\Dboard\Handlers\Languages\Toggle as LanguagesToggle;
use Blackcube\Dboard\Handlers\Languages\ToggleInit as LanguagesToggleInit;
use Blackcube\Dboard\Handlers\Languages\Index as LanguagesIndex;
use Blackcube\Dboard\Handlers\Hosts\Create as HostsCreate;
use Blackcube\Dboard\Handlers\Hosts\Delete as HostsDelete;
use Blackcube\Dboard\Handlers\Hosts\DeleteInit as HostsDeleteInit;
use Blackcube\Dboard\Handlers\Hosts\Edit as HostsEdit;
use Blackcube\Dboard\Handlers\Hosts\Toggle as HostsToggle;
use Blackcube\Dboard\Handlers\Hosts\ToggleInit as HostsToggleInit;
use Blackcube\Dboard\Handlers\Hosts\Index as HostsIndex;
use Blackcube\Dboard\Handlers\Slugs\Create as SlugsCreate;
use Blackcube\Dboard\Handlers\Slugs\Delete as SlugsDelete;
use Blackcube\Dboard\Handlers\Slugs\DeleteInit as SlugsDeleteInit;
use Blackcube\Dboard\Handlers\Slugs\Edit as SlugsEdit;
use Blackcube\Dboard\Handlers\Slugs\Toggle as SlugsToggle;
use Blackcube\Dboard\Handlers\Slugs\ToggleInit as SlugsToggleInit;
use Blackcube\Dboard\Handlers\Slugs\Index as SlugsIndex;
use Blackcube\Dboard\Handlers\Menus\Create as MenusCreate;
use Blackcube\Dboard\Handlers\Menus\Delete as MenusDelete;
use Blackcube\Dboard\Handlers\Menus\DeleteInit as MenusDeleteInit;
use Blackcube\Dboard\Handlers\Menus\Edit as MenusEdit;
use Blackcube\Dboard\Handlers\Menus\Move as MenusMove;
use Blackcube\Dboard\Handlers\Menus\Toggle as MenusToggle;
use Blackcube\Dboard\Handlers\Menus\ToggleInit as MenusToggleInit;
use Blackcube\Dboard\Handlers\Menus\Index as MenusIndex;
use Blackcube\Dboard\Handlers\LlmMenus\Create as LlmMenusCreate;
use Blackcube\Dboard\Handlers\LlmMenus\Delete as LlmMenusDelete;
use Blackcube\Dboard\Handlers\LlmMenus\DeleteInit as LlmMenusDeleteInit;
use Blackcube\Dboard\Handlers\LlmMenus\Edit as LlmMenusEdit;
use Blackcube\Dboard\Handlers\LlmMenus\Move as LlmMenusMove;
use Blackcube\Dboard\Handlers\LlmMenus\Index as LlmMenusIndex;
use Blackcube\Dboard\Handlers\Tags\BlocAdd as TagsBlocAdd;
use Blackcube\Dboard\Handlers\Tags\BlocDelete as TagsBlocDelete;
use Blackcube\Dboard\Handlers\Tags\BlocDeleteInit as TagsBlocDeleteInit;
use Blackcube\Dboard\Handlers\Tags\BlocReorder as TagsBlocReorder;
use Blackcube\Dboard\Handlers\Tags\Create as TagsCreate;
use Blackcube\Dboard\Handlers\Tags\Delete as TagsDelete;
use Blackcube\Dboard\Handlers\Tags\DeleteInit as TagsDeleteInit;
use Blackcube\Dboard\Handlers\Tags\Edit as TagsEdit;
use Blackcube\Dboard\Handlers\Tags\Move as TagsMove;
use Blackcube\Dboard\Handlers\Tags\Elastic as TagsElastic;
use Blackcube\Dboard\Handlers\Tags\Xeo as TagsXeo;
use Blackcube\Dboard\Handlers\Tags\XeoAuthors as TagsXeoAuthors;
use Blackcube\Dboard\Handlers\Tags\XeoLlm as TagsXeoLlm;
use Blackcube\Dboard\Handlers\Tags\XeoRefresh as TagsXeoRefresh;
use Blackcube\Dboard\Handlers\Tags\XeoSuggest as TagsXeoSuggest;
use Blackcube\Dboard\Handlers\Tags\SlugSitemap as TagsSlugSitemap;
use Blackcube\Dboard\Handlers\Tags\SlugGenerator as TagsSlugGenerator;
use Blackcube\Dboard\Handlers\Tags\Toggle as TagsToggle;
use Blackcube\Dboard\Handlers\Tags\ToggleInit as TagsToggleInit;
use Blackcube\Dboard\Handlers\Tags\Index as TagsIndex;
use Blackcube\Dboard\Handlers\Tags\Export as TagsExport;
use Blackcube\Dboard\Handlers\Tags\MdExport as TagsMdExport;
use Blackcube\Dboard\Handlers\Tags\MdImport as TagsMdImport;
use Blackcube\Dboard\Handlers\Tags\Translations as TagsTranslations;
use Blackcube\Dboard\Handlers\Contents\BlocAdd as ContentsBlocAdd;
use Blackcube\Dboard\Handlers\Contents\BlocDelete as ContentsBlocDelete;
use Blackcube\Dboard\Handlers\Contents\BlocDeleteInit as ContentsBlocDeleteInit;
use Blackcube\Dboard\Handlers\Contents\BlocReorder as ContentsBlocReorder;
use Blackcube\Dboard\Handlers\Contents\Create as ContentsCreate;
use Blackcube\Dboard\Handlers\Contents\Delete as ContentsDelete;
use Blackcube\Dboard\Handlers\Contents\DeleteInit as ContentsDeleteInit;
use Blackcube\Dboard\Handlers\Contents\Edit as ContentsEdit;
use Blackcube\Dboard\Handlers\Contents\Elastic as ContentsElastic;
use Blackcube\Dboard\Handlers\Contents\Index as ContentsIndex;
use Blackcube\Dboard\Handlers\Contents\Move as ContentsMove;
use Blackcube\Dboard\Handlers\Contents\Xeo as ContentsXeo;
use Blackcube\Dboard\Handlers\Contents\XeoAuthors as ContentsXeoAuthors;
use Blackcube\Dboard\Handlers\Contents\XeoLlm as ContentsXeoLlm;
use Blackcube\Dboard\Handlers\Contents\XeoRefresh as ContentsXeoRefresh;
use Blackcube\Dboard\Handlers\Contents\XeoSuggest as ContentsXeoSuggest;
use Blackcube\Dboard\Handlers\Contents\SlugSitemap as ContentsSlugSitemap;
use Blackcube\Dboard\Handlers\Contents\SlugGenerator as ContentsSlugGenerator;
use Blackcube\Dboard\Handlers\Contents\Tags as ContentsTags;
use Blackcube\Dboard\Handlers\Contents\Toggle as ContentsToggle;
use Blackcube\Dboard\Handlers\Contents\ToggleInit as ContentsToggleInit;
use Blackcube\Dboard\Handlers\Contents\Translations as ContentsTranslations;
use Blackcube\Dboard\Handlers\Contents\Export as ContentsExport;
use Blackcube\Dboard\Handlers\Contents\MdExport as ContentsMdExport;
use Blackcube\Dboard\Handlers\Contents\MdImport as ContentsMdImport;
use Blackcube\Dboard\Handlers\Import\Step1 as ImportStep1;
use Blackcube\Dboard\Handlers\Import\Step2 as ImportStep2;
use Blackcube\Dboard\Handlers\Import\Step3 as ImportStep3;
use Blackcube\Dboard\Handlers\Import\Step4 as ImportStep4;
use Blackcube\Dboard\Handlers\Parameters\Create as ParametersCreate;
use Blackcube\Dboard\Handlers\Parameters\Delete as ParametersDelete;
use Blackcube\Dboard\Handlers\Parameters\DeleteInit as ParametersDeleteInit;
use Blackcube\Dboard\Handlers\Parameters\Edit as ParametersEdit;
use Blackcube\Dboard\Handlers\Parameters\Index as ParametersIndex;
use Blackcube\Dboard\Handlers\Authors\Create as AuthorsCreate;
use Blackcube\Dboard\Handlers\Authors\Delete as AuthorsDelete;
use Blackcube\Dboard\Handlers\Authors\DeleteInit as AuthorsDeleteInit;
use Blackcube\Dboard\Handlers\Authors\Edit as AuthorsEdit;
use Blackcube\Dboard\Handlers\Authors\Index as AuthorsIndex;
use Blackcube\Dboard\Handlers\Authors\Toggle as AuthorsToggle;
use Blackcube\Dboard\Handlers\Authors\ToggleInit as AuthorsToggleInit;
use Blackcube\Dboard\Handlers\ElasticSchemas\Create as ElasticSchemasCreate;
use Blackcube\Dboard\Handlers\ElasticSchemas\Delete as ElasticSchemasDelete;
use Blackcube\Dboard\Handlers\ElasticSchemas\DeleteInit as ElasticSchemasDeleteInit;
use Blackcube\Dboard\Handlers\ElasticSchemas\Edit as ElasticSchemasEdit;
use Blackcube\Dboard\Handlers\ElasticSchemas\Index as ElasticSchemasIndex;
use Blackcube\Dboard\Handlers\Types\Create as TypesCreate;
use Blackcube\Dboard\Handlers\Types\Delete as TypesDelete;
use Blackcube\Dboard\Handlers\Types\DeleteInit as TypesDeleteInit;
use Blackcube\Dboard\Handlers\Types\Edit as TypesEdit;
use Blackcube\Dboard\Handlers\Types\Index as TypesIndex;
use Blackcube\Dboard\Handlers\Rbac\Index as RbacIndex;
use Blackcube\Dboard\Handlers\Rbac\Refresh as RbacRefresh;
use Blackcube\Dboard\Handlers\Rbac\RefreshInit as RbacRefreshInit;
use Blackcube\Dboard\Handlers\Rbac\View as RbacView;
use Blackcube\Dboard\Handlers\Onboarding\Finish;
use Blackcube\Dboard\Handlers\Onboarding\Step1;
use Blackcube\Dboard\Handlers\Onboarding\Step2;
use Blackcube\Dboard\Handlers\Onboarding\Step3;
use Blackcube\FileProvider\Handlers\ResumableUploadHandler;
use Blackcube\FileProvider\Handlers\ResumablePreviewHandler;
use Blackcube\FileProvider\Handlers\ResumableDeleteHandler;
use Blackcube\Dboard\Handlers\Xeo\OrganizationIndex as XeoOrganizationIndex;
use Blackcube\Dboard\Handlers\Xeo\OrganizationEdit as XeoOrganizationEdit;
use Blackcube\Dboard\Handlers\Xeo\WebSiteIndex as XeoWebSiteIndex;
use Blackcube\Dboard\Handlers\Xeo\WebSiteEdit as XeoWebSiteEdit;
use Blackcube\Dboard\Handlers\Xeo\RobotsIndex as XeoRobotsIndex;
use Blackcube\Dboard\Handlers\Xeo\RobotsEdit as XeoRobotsEdit;
use Blackcube\Dboard\Handlers\Xeo\SitemapIndex as XeoSitemapIndex;
use Blackcube\Dboard\Handlers\Xeo\SitemapEdit as XeoSitemapEdit;
use Blackcube\Dboard\Handlers\Xeo\OrganizationToggle as XeoOrganizationToggle;
use Blackcube\Dboard\Handlers\Xeo\OrganizationDelete as XeoOrganizationDelete;
use Blackcube\Dboard\Handlers\Xeo\WebSiteToggle as XeoWebSiteToggle;
use Blackcube\Dboard\Handlers\Xeo\WebSiteDelete as XeoWebSiteDelete;
use Blackcube\Dboard\Handlers\Xeo\RobotsToggle as XeoRobotsToggle;
use Blackcube\Dboard\Handlers\Xeo\RobotsDelete as XeoRobotsDelete;
use Blackcube\Dboard\Handlers\Xeo\SitemapToggle as XeoSitemapToggle;
use Blackcube\Dboard\Handlers\Xeo\SitemapDelete as XeoSitemapDelete;
use Blackcube\Dboard\Handlers\SchemasMapping\Index as SchemasMappingIndex;
use Blackcube\Dboard\Handlers\SchemasMapping\Create as SchemasMappingCreate;
use Blackcube\Dboard\Handlers\SchemasMapping\Edit as SchemasMappingEdit;
use Blackcube\Dboard\Handlers\SchemasMapping\Delete as SchemasMappingDelete;
use Blackcube\Dboard\Handlers\SchemasMapping\DeleteInit as SchemasMappingDeleteInit;
use Blackcube\Dboard\Handlers\Preview\Toggle as PreviewToggle;
use Blackcube\Dboard\Components\Rbac;
use Blackcube\Dboard\Middlewares\JsonBodyParserMiddleware;
use Blackcube\Dboard\Middlewares\JwtAuthMiddleware;
use Blackcube\Dboard\Middlewares\LocaleMiddleware;
use Blackcube\Dboard\Middlewares\MultiVerbParserMiddleware;
use Yiisoft\Csrf\CsrfTokenMiddleware;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    Group::create($params['blackcube/dboard']['routePrefix'] ?? '/dboard')
        ->middleware(CsrfTokenMiddleware::class)
        ->middleware(JsonBodyParserMiddleware::class)
        ->middleware(LocaleMiddleware::class)
        ->routes(
            Route::get('[/]')
                ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_SITE_DASHBOARD]))
                ->action(DashboardIndex::class),
            Route::methods(['GET', 'POST'], '/login')
                ->action(Login::class)
                ->name('dboard.login'),
            Route::methods(['GET', 'POST'], '/logout')
                ->action(Logout::class)
                ->name('dboard.logout'),
            Route::methods(['GET', 'POST'], '/onboarding/step1')
                ->action(Step1::class)
                ->name('dboard.onboarding.step1'),
            Route::get('/onboarding/step2')
                ->action(Step2::class)
                ->name('dboard.onboarding.step2'),
            Route::get('/onboarding/step3')
                ->action(Step3::class)
                ->name('dboard.onboarding.step3'),
            Route::get('/onboarding/finish')
                ->action(Finish::class)
                ->name('dboard.onboarding.finish'),
            Route::get('/dashboard')
                ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_SITE_DASHBOARD]))
                ->action(DashboardIndex::class)
                ->name('dboard.dashboard'),
            Group::create('/account')
                ->middleware(JwtAuthMiddleware::class)
                ->routes(
                    Route::methods(['GET', 'POST'], '/{id:\d+}')
                        ->middleware(MultiVerbParserMiddleware::class)
                        ->action(AccountIndex::class)
                        ->name('dboard.account'),
                    Route::get('/{id:\d+}/passkeys[/]')
                        ->action(AccountPasskeysInit::class)
                        ->name('dboard.account.passkeys.init'),
                    Route::methods(['POST', 'DELETE'], '/{id:\d+}/passkeys[/]')
                        ->middleware(MultiVerbParserMiddleware::class)
                        ->action(AccountPasskeys::class)
                        ->name('dboard.account.passkeys'),
                ),
            Group::create('/auth')
                ->routes(
                    Route::post('/challenges')
                        ->middleware(JwtAuthMiddleware::optional())
                        ->action(AuthChallenges::class)
                        ->name('dboard.auth.challenges'),
                    Route::post('/token')
                        ->action(AuthToken::class)
                        ->name('dboard.auth.token'),
                ),
            Group::create('/administrators')
                ->routes(
                    Route::get('[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_ADMINISTRATOR_VIEW]))
                        ->action(AdministratorsIndex::class)
                        ->name('dboard.administrators'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/edit')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_ADMINISTRATOR_UPDATE]))
                        ->action(AdministratorsEdit::class)
                        ->name('dboard.administrators.edit'),
                    Route::get('/{id:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_ADMINISTRATOR_UPDATE]))
                        ->action(AdministratorsToggleInit::class)
                        ->name('dboard.administrators.toggle'),
                    Route::post('/{id:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_ADMINISTRATOR_UPDATE]))
                        ->action(AdministratorsToggle::class),
                    Route::methods(['GET', 'POST'], '/create')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_ADMINISTRATOR_CREATE]))
                        ->action(AdministratorsCreate::class)
                        ->name('dboard.administrators.create'),
                    Route::get('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_ADMINISTRATOR_DELETE]))
                        ->action(AdministratorsDeleteInit::class)
                        ->name('dboard.administrators.delete'),
                    Route::delete('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_ADMINISTRATOR_DELETE]))
                        ->action(AdministratorsDelete::class),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/permissions')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_ADMINISTRATOR_UPDATE]))
                        ->action(AdministratorsPermissions::class)
                        ->name('dboard.administrators.permissions'),
                ),
            Group::create('/languages')
                ->routes(
                    Route::get('[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_LANGUAGE_VIEW]))
                        ->action(LanguagesIndex::class)
                        ->name('dboard.languages'),
                    Route::methods(['GET', 'POST'], '/{id}/edit')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_LANGUAGE_UPDATE]))
                        ->action(LanguagesEdit::class)
                        ->name('dboard.languages.edit'),
                    Route::get('/{id}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_LANGUAGE_UPDATE]))
                        ->action(LanguagesToggleInit::class)
                        ->name('dboard.languages.toggle'),
                    Route::post('/{id}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_LANGUAGE_UPDATE]))
                        ->action(LanguagesToggle::class),
                    Route::methods(['GET', 'POST'], '/create')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_LANGUAGE_CREATE]))
                        ->action(LanguagesCreate::class)
                        ->name('dboard.languages.create'),
                    Route::get('/{id}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_LANGUAGE_DELETE]))
                        ->action(LanguagesDeleteInit::class)
                        ->name('dboard.languages.delete'),
                    Route::delete('/{id}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_LANGUAGE_DELETE]))
                        ->action(LanguagesDelete::class),
                ),
            Group::create('/hosts')
                ->routes(
                    Route::get('[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_HOST_VIEW]))
                        ->action(HostsIndex::class)
                        ->name('dboard.hosts'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/edit')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_HOST_UPDATE]))
                        ->action(HostsEdit::class)
                        ->name('dboard.hosts.edit'),
                    Route::get('/{id:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_HOST_UPDATE]))
                        ->action(HostsToggleInit::class)
                        ->name('dboard.hosts.toggle'),
                    Route::post('/{id:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_HOST_UPDATE]))
                        ->action(HostsToggle::class),
                    Route::methods(['GET', 'POST'], '/create')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_HOST_CREATE]))
                        ->action(HostsCreate::class)
                        ->name('dboard.hosts.create'),
                    Route::get('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_HOST_DELETE]))
                        ->action(HostsDeleteInit::class)
                        ->name('dboard.hosts.delete'),
                    Route::delete('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_HOST_DELETE]))
                        ->action(HostsDelete::class),
                ),
            Group::create('/parameters')
                ->routes(
                    Route::get('[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_PARAMETER_VIEW]))
                        ->action(ParametersIndex::class)
                        ->name('dboard.parameters'),
                    Route::methods(['GET', 'POST'], '/create')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_PARAMETER_CREATE]))
                        ->action(ParametersCreate::class)
                        ->name('dboard.parameters.create'),
                    Route::methods(['GET', 'POST'], '/domains/{domain}/names/{name}/edit')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_PARAMETER_UPDATE]))
                        ->action(ParametersEdit::class)
                        ->name('dboard.parameters.edit'),
                    Route::get('/domains/{domain}/names/{name}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_PARAMETER_DELETE]))
                        ->action(ParametersDeleteInit::class)
                        ->name('dboard.parameters.delete'),
                    Route::delete('/domains/{domain}/names/{name}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_PARAMETER_DELETE]))
                        ->action(ParametersDelete::class),
                ),
            Group::create('/authors')
                ->routes(
                    Route::get('[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_AUTHOR_VIEW]))
                        ->action(AuthorsIndex::class)
                        ->name('dboard.authors'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/edit')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_AUTHOR_UPDATE]))
                        ->action(AuthorsEdit::class)
                        ->name('dboard.authors.edit'),
                    Route::get('/{id:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_AUTHOR_UPDATE]))
                        ->action(AuthorsToggleInit::class)
                        ->name('dboard.authors.toggle'),
                    Route::post('/{id:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_AUTHOR_UPDATE]))
                        ->action(AuthorsToggle::class),
                    Route::methods(['GET', 'POST'], '/create')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_AUTHOR_CREATE]))
                        ->action(AuthorsCreate::class)
                        ->name('dboard.authors.create'),
                    Route::get('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_AUTHOR_DELETE]))
                        ->action(AuthorsDeleteInit::class)
                        ->name('dboard.authors.delete'),
                    Route::delete('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_AUTHOR_DELETE]))
                        ->action(AuthorsDelete::class),
                    Route::methods(['GET', 'POST'], '/files/upload')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_AUTHOR_UPDATE]))
                        ->action(ResumableUploadHandler::class)
                        ->name('dboard.authors.files.upload'),
                    Route::methods(['GET'], '/files/preview')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_AUTHOR_UPDATE, Rbac::PERMISSION_AUTHOR_VIEW]))
                        ->action(ResumablePreviewHandler::class)
                        ->name('dboard.authors.files.preview'),
                    Route::methods(['DELETE'], '/files/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_AUTHOR_UPDATE]))
                        ->action(ResumableDeleteHandler::class)
                        ->name('dboard.authors.files.delete'),
                ),
            Group::create('/types')
                ->routes(
                    Route::get('[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TYPE_VIEW]))
                        ->action(TypesIndex::class)
                        ->name('dboard.types'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/edit')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TYPE_UPDATE]))
                        ->action(TypesEdit::class)
                        ->name('dboard.types.edit'),
                    Route::methods(['GET', 'POST'], '/create')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TYPE_CREATE]))
                        ->action(TypesCreate::class)
                        ->name('dboard.types.create'),
                    Route::get('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TYPE_DELETE]))
                        ->action(TypesDeleteInit::class)
                        ->name('dboard.types.delete'),
                    Route::delete('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TYPE_DELETE]))
                        ->action(TypesDelete::class),
                ),
            Group::create('/elasticschemas')
                ->routes(
                    Route::get('[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_ELASTICSCHEMA_VIEW]))
                        ->action(ElasticSchemasIndex::class)
                        ->name('dboard.elasticschemas'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/edit')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_ELASTICSCHEMA_UPDATE]))
                        ->action(ElasticSchemasEdit::class)
                        ->name('dboard.elasticschemas.edit'),
                    Route::methods(['GET', 'POST'], '/create')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_ELASTICSCHEMA_CREATE]))
                        ->action(ElasticSchemasCreate::class)
                        ->name('dboard.elasticschemas.create'),
                    Route::get('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_ELASTICSCHEMA_DELETE]))
                        ->action(ElasticSchemasDeleteInit::class)
                        ->name('dboard.elasticschemas.delete'),
                    Route::delete('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_ELASTICSCHEMA_DELETE]))
                        ->action(ElasticSchemasDelete::class),
                ),
            Group::create('/rbac')
                ->routes(
                    Route::get('[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_RBAC_VIEW]))
                        ->action(RbacIndex::class)
                        ->name('dboard.rbac'),
                    Route::get('/view')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_RBAC_VIEW]))
                        ->action(RbacView::class)
                        ->name('dboard.rbac.view'),
                    Route::get('/refresh')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_RBAC_REFRESH]))
                        ->action(RbacRefreshInit::class)
                        ->name('dboard.rbac.refresh'),
                    Route::post('/refresh')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_RBAC_REFRESH]))
                        ->action(RbacRefresh::class),
                ),
            Group::create('/menus')
                ->routes(
                    Route::methods(['GET', 'POST'], '[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_MENU_VIEW]))
                        ->action(MenusIndex::class)
                        ->name('dboard.menus'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/edit')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_MENU_UPDATE]))
                        ->action(MenusEdit::class)
                        ->name('dboard.menus.edit'),
                    Route::get('/{id:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_MENU_UPDATE]))
                        ->action(MenusToggleInit::class)
                        ->name('dboard.menus.toggle'),
                    Route::post('/{id:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_MENU_UPDATE]))
                        ->action(MenusToggle::class),
                    Route::methods(['GET', 'POST'], '/create')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_MENU_CREATE]))
                        ->action(MenusCreate::class)
                        ->name('dboard.menus.create'),
                    Route::get('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_MENU_DELETE]))
                        ->action(MenusDeleteInit::class)
                        ->name('dboard.menus.delete'),
                    Route::delete('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_MENU_DELETE]))
                        ->action(MenusDelete::class),
                    Route::post('/move')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_MENU_UPDATE]))
                        ->action(MenusMove::class)
                        ->name('dboard.menus.move'),
                ),
            Group::create('/llm-menus')
                ->routes(
                    Route::methods(['GET', 'POST'], '[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_LLMMENU_VIEW]))
                        ->action(LlmMenusIndex::class)
                        ->name('dboard.llmmenus'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/edit')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_LLMMENU_UPDATE]))
                        ->action(LlmMenusEdit::class)
                        ->name('dboard.llmmenus.edit'),
                    Route::methods(['GET', 'POST'], '/create')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_LLMMENU_CREATE]))
                        ->action(LlmMenusCreate::class)
                        ->name('dboard.llmmenus.create'),
                    Route::get('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_LLMMENU_DELETE]))
                        ->action(LlmMenusDeleteInit::class)
                        ->name('dboard.llmmenus.delete'),
                    Route::delete('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_LLMMENU_DELETE]))
                        ->action(LlmMenusDelete::class),
                    Route::post('/move')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_LLMMENU_UPDATE]))
                        ->action(LlmMenusMove::class)
                        ->name('dboard.llmmenus.move'),
                ),
            Group::create('/tags')
                ->routes(
                    Route::methods(['GET', 'POST'], '[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_VIEW]))
                        ->action(TagsIndex::class)
                        ->name('dboard.tags'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/edit')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->action(TagsEdit::class)
                        ->name('dboard.tags.edit'),
                    Route::methods(['GET', 'POST'], '/create')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_CREATE]))
                        ->action(TagsCreate::class)
                        ->name('dboard.tags.create'),
                    Route::get('/{id:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->action(TagsToggleInit::class)
                        ->name('dboard.tags.toggle'),
                    Route::post('/{id:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->action(TagsToggle::class),
                    Route::get('/{id:\d+}/export')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_EXPORT]))
                        ->action(TagsExport::class)
                        ->name('dboard.tags.export'),
                    Route::get('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_DELETE]))
                        ->action(TagsDeleteInit::class)
                        ->name('dboard.tags.delete'),
                    Route::delete('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_DELETE]))
                        ->action(TagsDelete::class),
                    Route::post('/move')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->action(TagsMove::class)
                        ->name('dboard.tags.move'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/elastic')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->action(TagsElastic::class)
                        ->name('dboard.tags.elastic'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/slug-sitemap')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->action(TagsSlugSitemap::class)
                        ->name('dboard.tags.slug-sitemap'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/xeo')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->action(TagsXeo::class)
                        ->name('dboard.tags.xeo'),
                    Route::post('/{id:\d+}/xeo/refresh')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->action(TagsXeoRefresh::class)
                        ->name('dboard.tags.xeo.refresh'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/xeo/suggest')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->action(TagsXeoSuggest::class)
                        ->name('dboard.tags.xeo.suggest'),
                    Route::post('/{id:\d+}/xeo/authors')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->action(TagsXeoAuthors::class)
                        ->name('dboard.tags.xeo.authors'),
                    Route::methods(['GET', 'POST', 'DELETE'], '/{id:\d+}/xeo/llm')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->middleware(MultiVerbParserMiddleware::class)
                        ->action(TagsXeoLlm::class)
                        ->name('dboard.tags.xeo.llm'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/md-export')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_IA_EXPORT]))
                        ->action(TagsMdExport::class)
                        ->name('dboard.tags.md-export'),
                    Route::post('/{id:\d+}/md-import')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_IA_IMPORT]))
                        ->action(TagsMdImport::class)
                        ->name('dboard.tags.md-import'),
                    Route::post('/{id:\d+}/blocs[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->action(TagsBlocAdd::class)
                        ->name('dboard.tags.blocs'),
                    Route::post('/{id:\d+}/blocs/reorder')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->action(TagsBlocReorder::class)
                        ->name('dboard.tags.blocs.reorder'),
                    Route::get('/{id:\d+}/blocs/{blocId:\d+}')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->action(TagsBlocDeleteInit::class)
                        ->name('dboard.tags.bloc'),
                    Route::delete('/{id:\d+}/blocs/{blocId:\d+}')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->action(TagsBlocDelete::class),
                    Route::methods(['GET', 'POST'], '/files/upload')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->action(ResumableUploadHandler::class)
                        ->name('dboard.tags.files.upload'),
                    Route::methods(['GET'], '/files/preview')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE, Rbac::PERMISSION_TAG_VIEW]))
                        ->action(ResumablePreviewHandler::class)
                        ->name('dboard.tags.files.preview'),
                    Route::methods(['DELETE'], '/files/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->action(ResumableDeleteHandler::class)
                        ->name('dboard.tags.files.delete'),
                    Route::methods(['GET'], '/{id:\d+}/slug-generator')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->action(TagsSlugGenerator::class)
                        ->name('dboard.tags.slug-generator'),
                    Route::methods(['GET', 'POST', 'DELETE'], '/{id:\d+}/translations')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_TAG_UPDATE]))
                        ->middleware(MultiVerbParserMiddleware::class)
                        ->action(TagsTranslations::class)
                        ->name('dboard.tags.translations'),
                ),
            Group::create('/contents')
                ->routes(
                    Route::methods(['GET', 'POST'], '[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_VIEW]))
                        ->action(ContentsIndex::class)
                        ->name('dboard.contents'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/edit')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ContentsEdit::class)
                        ->name('dboard.contents.edit'),
                    Route::methods(['GET', 'POST'], '/create')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_CREATE]))
                        ->action(ContentsCreate::class)
                        ->name('dboard.contents.create'),
                    Route::get('/{id:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ContentsToggleInit::class)
                        ->name('dboard.contents.toggle'),
                    Route::post('/{id:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ContentsToggle::class),
                    Route::get('/{id:\d+}/export')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_EXPORT]))
                        ->action(ContentsExport::class)
                        ->name('dboard.contents.export'),
                    Route::get('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_DELETE]))
                        ->action(ContentsDeleteInit::class)
                        ->name('dboard.contents.delete'),
                    Route::delete('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_DELETE]))
                        ->action(ContentsDelete::class),
                    Route::post('/move')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ContentsMove::class)
                        ->name('dboard.contents.move'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/elastic')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ContentsElastic::class)
                        ->name('dboard.contents.elastic'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/slug-sitemap')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ContentsSlugSitemap::class)
                        ->name('dboard.contents.slug-sitemap'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/tags')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ContentsTags::class)
                        ->name('dboard.contents.tags'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/xeo')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ContentsXeo::class)
                        ->name('dboard.contents.xeo'),
                    Route::post('/{id:\d+}/xeo/refresh')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ContentsXeoRefresh::class)
                        ->name('dboard.contents.xeo.refresh'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/xeo/suggest')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ContentsXeoSuggest::class)
                        ->name('dboard.contents.xeo.suggest'),
                    Route::post('/{id:\d+}/xeo/authors')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ContentsXeoAuthors::class)
                        ->name('dboard.contents.xeo.authors'),
                    Route::methods(['GET', 'POST', 'DELETE'], '/{id:\d+}/xeo/llm')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->middleware(MultiVerbParserMiddleware::class)
                        ->action(ContentsXeoLlm::class)
                        ->name('dboard.contents.xeo.llm'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/md-export')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_IA_EXPORT]))
                        ->action(ContentsMdExport::class)
                        ->name('dboard.contents.md-export'),
                    Route::post('/{id:\d+}/md-import')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_IA_IMPORT]))
                        ->action(ContentsMdImport::class)
                        ->name('dboard.contents.md-import'),
                    Route::methods(['GET', 'POST', 'DELETE'], '/{id:\d+}/translations')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->middleware(MultiVerbParserMiddleware::class)
                        ->action(ContentsTranslations::class)
                        ->name('dboard.contents.translations'),
                    Route::post('/{id:\d+}/blocs[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ContentsBlocAdd::class)
                        ->name('dboard.contents.blocs'),
                    Route::post('/{id:\d+}/blocs/reorder')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ContentsBlocReorder::class)
                        ->name('dboard.contents.blocs.reorder'),
                    Route::get('/{id:\d+}/blocs/{blocId:\d+}')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ContentsBlocDeleteInit::class)
                        ->name('dboard.contents.bloc'),
                    Route::delete('/{id:\d+}/blocs/{blocId:\d+}')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ContentsBlocDelete::class),
                    Route::methods(['GET', 'POST'], '/files/upload')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ResumableUploadHandler::class)
                        ->name('dboard.contents.files.upload'),
                    Route::methods(['GET'], '/files/preview')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE, Rbac::PERMISSION_CONTENT_VIEW]))
                        ->action(ResumablePreviewHandler::class)
                        ->name('dboard.contents.files.preview'),
                    Route::methods(['DELETE'], '/files/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ResumableDeleteHandler::class)
                        ->name('dboard.contents.files.delete'),
                    Route::methods(['GET'], '/{id:\d+}/slug-generator')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_UPDATE]))
                        ->action(ContentsSlugGenerator::class)
                        ->name('dboard.contents.slug-generator'),
                ),
            // Gestion
            Group::create('/slugs')
                ->routes(
                    Route::get('[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_SLUG_VIEW]))
                        ->action(SlugsIndex::class)
                        ->name('dboard.slugs'),
                    Route::methods(['GET', 'POST'], '/{id:\d+}/edit')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_SLUG_UPDATE]))
                        ->action(SlugsEdit::class)
                        ->name('dboard.slugs.edit'),
                    Route::get('/{id:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_SLUG_UPDATE]))
                        ->action(SlugsToggleInit::class)
                        ->name('dboard.slugs.toggle'),
                    Route::post('/{id:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_SLUG_UPDATE]))
                        ->action(SlugsToggle::class),
                    Route::methods(['GET', 'POST'], '/create')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_SLUG_CREATE, Rbac::PERMISSION_SLUG_UPDATE]))
                        ->action(SlugsCreate::class)
                        ->name('dboard.slugs.create'),
                    Route::get('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_SLUG_DELETE]))
                        ->action(SlugsDeleteInit::class)
                        ->name('dboard.slugs.delete'),
                    Route::delete('/{id:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_SLUG_DELETE]))
                        ->action(SlugsDelete::class),
                ),
            Group::create('/xeo')
                ->routes(
                    Route::get('/organization[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_VIEW]))
                        ->action(XeoOrganizationIndex::class)
                        ->name('dboard.xeo.organization'),
                    Route::methods(['GET', 'POST'], '/organization/{hostId:\d+}')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_UPDATE]))
                        ->action(XeoOrganizationEdit::class)
                        ->name('dboard.xeo.organization.edit'),
                    Route::get('/organization/{hostId:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_UPDATE]))
                        ->action(XeoOrganizationToggle::class)
                        ->name('dboard.xeo.organization.toggle'),
                    Route::post('/organization/{hostId:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_UPDATE]))
                        ->action(XeoOrganizationToggle::class),
                    Route::get('/organization/{hostId:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_DELETE]))
                        ->action(XeoOrganizationDelete::class)
                        ->name('dboard.xeo.organization.delete'),
                    Route::delete('/organization/{hostId:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_DELETE]))
                        ->action(XeoOrganizationDelete::class),
                    Route::get('/website[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_VIEW]))
                        ->action(XeoWebSiteIndex::class)
                        ->name('dboard.xeo.website'),
                    Route::methods(['GET', 'POST'], '/website/{hostId:\d+}')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_UPDATE]))
                        ->action(XeoWebSiteEdit::class)
                        ->name('dboard.xeo.website.edit'),
                    Route::get('/website/{hostId:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_UPDATE]))
                        ->action(XeoWebSiteToggle::class)
                        ->name('dboard.xeo.website.toggle'),
                    Route::post('/website/{hostId:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_UPDATE]))
                        ->action(XeoWebSiteToggle::class),
                    Route::get('/website/{hostId:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_DELETE]))
                        ->action(XeoWebSiteDelete::class)
                        ->name('dboard.xeo.website.delete'),
                    Route::delete('/website/{hostId:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_DELETE]))
                        ->action(XeoWebSiteDelete::class),
                    Route::get('/robots[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_VIEW]))
                        ->action(XeoRobotsIndex::class)
                        ->name('dboard.xeo.robots'),
                    Route::methods(['GET', 'POST'], '/robots/{hostId:\d+}')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_UPDATE]))
                        ->action(XeoRobotsEdit::class)
                        ->name('dboard.xeo.robots.edit'),
                    Route::get('/robots/{hostId:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_UPDATE]))
                        ->action(XeoRobotsToggle::class)
                        ->name('dboard.xeo.robots.toggle'),
                    Route::post('/robots/{hostId:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_UPDATE]))
                        ->action(XeoRobotsToggle::class),
                    Route::get('/robots/{hostId:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_DELETE]))
                        ->action(XeoRobotsDelete::class)
                        ->name('dboard.xeo.robots.delete'),
                    Route::delete('/robots/{hostId:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_DELETE]))
                        ->action(XeoRobotsDelete::class),
                    Route::get('/sitemap[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_VIEW]))
                        ->action(XeoSitemapIndex::class)
                        ->name('dboard.xeo.sitemap'),
                    Route::methods(['GET', 'POST'], '/sitemap/{hostId:\d+}')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_UPDATE]))
                        ->action(XeoSitemapEdit::class)
                        ->name('dboard.xeo.sitemap.edit'),
                    Route::get('/sitemap/{hostId:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_UPDATE]))
                        ->action(XeoSitemapToggle::class)
                        ->name('dboard.xeo.sitemap.toggle'),
                    Route::post('/sitemap/{hostId:\d+}/toggle')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_UPDATE]))
                        ->action(XeoSitemapToggle::class),
                    Route::get('/sitemap/{hostId:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_DELETE]))
                        ->action(XeoSitemapDelete::class)
                        ->name('dboard.xeo.sitemap.delete'),
                    Route::delete('/sitemap/{hostId:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_DELETE]))
                        ->action(XeoSitemapDelete::class),
                    Route::get('/mapping[/]')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_VIEW]))
                        ->action(SchemasMappingIndex::class)
                        ->name('dboard.xeo.mapping'),
                    Route::methods(['GET', 'POST'], '/mapping/create')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_CREATE]))
                        ->action(SchemasMappingCreate::class)
                        ->name('dboard.xeo.mapping.create'),
                    Route::methods(['GET', 'POST'], '/mapping/{regularElasticSchemaId:\d+}/{xeoElasticSchemaId:\d+}/edit')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_UPDATE]))
                        ->action(SchemasMappingEdit::class)
                        ->name('dboard.xeo.mapping.edit'),
                    Route::get('/mapping/{regularElasticSchemaId:\d+}/{xeoElasticSchemaId:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_DELETE]))
                        ->action(SchemasMappingDeleteInit::class)
                        ->name('dboard.xeo.mapping.delete'),
                    Route::delete('/mapping/{regularElasticSchemaId:\d+}/{xeoElasticSchemaId:\d+}/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_DELETE]))
                        ->action(SchemasMappingDelete::class),
                    Route::methods(['GET', 'POST'], '/files/upload')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_UPDATE]))
                        ->action(ResumableUploadHandler::class)
                        ->name('dboard.xeo.files.upload'),
                    Route::methods(['GET'], '/files/preview')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_VIEW]))
                        ->action(ResumablePreviewHandler::class)
                        ->name('dboard.xeo.files.preview'),
                    Route::methods(['DELETE'], '/files/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_XEO_UPDATE]))
                        ->action(ResumableDeleteHandler::class)
                        ->name('dboard.xeo.files.delete'),
                ),
            Group::create('/import')
                ->routes(
                    Route::methods(['GET', 'POST'], '/step1')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_IMPORT, Rbac::PERMISSION_TAG_IMPORT]))
                        ->action(ImportStep1::class)
                        ->name('dboard.import.step1'),
                    Route::methods(['GET', 'POST'], '/step2')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_IMPORT, Rbac::PERMISSION_TAG_IMPORT]))
                        ->action(ImportStep2::class)
                        ->name('dboard.import.step2'),
                    Route::methods(['GET', 'POST'], '/step3')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_IMPORT, Rbac::PERMISSION_TAG_IMPORT]))
                        ->action(ImportStep3::class)
                        ->name('dboard.import.step3'),
                    Route::methods(['GET', 'POST'], '/step4')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_IMPORT, Rbac::PERMISSION_TAG_IMPORT]))
                        ->action(ImportStep4::class)
                        ->name('dboard.import.step4'),
                    Route::methods(['GET', 'POST'], '/files/upload')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_IMPORT, Rbac::PERMISSION_TAG_IMPORT]))
                        ->action(ResumableUploadHandler::class)
                        ->name('dboard.import.files.upload'),
                    Route::methods(['GET'], '/files/preview')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_IMPORT, Rbac::PERMISSION_TAG_IMPORT]))
                        ->action(ResumablePreviewHandler::class)
                        ->name('dboard.import.files.preview'),
                    Route::methods(['DELETE'], '/files/delete')
                        ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_CONTENT_IMPORT, Rbac::PERMISSION_TAG_IMPORT]))
                        ->action(ResumableDeleteHandler::class)
                        ->name('dboard.import.files.delete'),
                ),
            Route::methods(['GET', 'POST'], '/search')
                ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_SITE_SEARCH]))
                ->action(SearchIndex::class)
                ->name('dboard.search'),
            Route::post('/preview/toggle')
                ->middleware(JwtAuthMiddleware::withRbac([Rbac::PERMISSION_SITE_PREVIEW]))
                ->action(PreviewToggle::class)
                ->name('dboard.preview.toggle'),
            // Plugins
            Route::get('/plugins')
                ->middleware(JwtAuthMiddleware::class)
                ->action(DashboardIndex::class)
                ->name('dboard.plugins'),
        ),
];
