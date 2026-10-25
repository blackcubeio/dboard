<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Tests\Integration;

use Blackcube\Dcore\Services\MapperService;
use Blackcube\Dcore\Services\ImportService;
use Blackcube\Dboard\Tests\Support\DatabaseCestTrait;
use Blackcube\Dboard\Tests\Support\IntegrationTester;
use Blackcube\Dcore\Models\Author;
use Blackcube\Dcore\Models\Bloc;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\ContentAuthor;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Models\Sitemap;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dcore\Models\Tag;
use Blackcube\Dcore\Models\TagAuthor;
use Blackcube\Dcore\Models\Type;
use Blackcube\Dcore\Models\Xeo;
use Blackcube\Dcore\Services\FileService;
use Blackcube\FileProvider\FileProvider;
use Blackcube\FileProvider\Flysystem\FlysystemLocal;
use Yiisoft\Aliases\Aliases;

/**
 * Real integration round-trip for content/tag export + import.
 *
 * No mock, no stub, no double: every service is the real implementation and
 * every model is persisted in the test database.
 *
 * Round-trip strategy: a rich element is built and persisted, exported, then
 * DELETED (real cascade, which frees its slug URL because slugs carry a UNIQUE
 * (hostId, path) index - see M000000000004CreateSlugs), then re-imported in
 * 'create' mode. Deleting the source isolates the serialization round-trip from
 * the URL-uniqueness business rule, so the re-imported element keeps the very
 * same slug URL. The second export must then equal the first one once volatile
 * keys (database ids, timestamps and the Hazeltree path) are stripped.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ExportImportRoundTripCest
{
    use DatabaseCestTrait;

    private MapperService $mapperService;
    private ImportService $importService;

    private function bootServices(): void
    {
        $blfsDir = sys_get_temp_dir() . '/blfs-' . uniqid();
        $bltmpDir = sys_get_temp_dir() . '/bltmp-' . uniqid();
        mkdir($blfsDir, 0777, true);
        mkdir($bltmpDir, 0777, true);

        $fileProvider = new FileProvider(new Aliases());
        $fileProvider->addFilesystem(FileProvider::ALIAS_FS, new FlysystemLocal($blfsDir));
        $fileProvider->addFilesystem(FileProvider::ALIAS_TMP, new FlysystemLocal($bltmpDir));

        $fileService = new FileService($fileProvider);
        $this->mapperService = new MapperService($fileService);
        $this->importService = new ImportService($this->db, $fileService, $this->mapperService);
    }

    private function createLanguage(): Language
    {
        $language = Language::query()->andWhere(['id' => 'fr'])->one();
        if ($language === null) {
            $language = new Language();
            $language->setId('fr');
            $language->setName('Francais');
            $language->setActive(true);
            $language->setMain(true);
            $language->save();
        }
        return $language;
    }

    private function createType(): Type
    {
        $type = new Type();
        $type->setName('Page ' . uniqid());
        $type->setHandler('test');
        $type->save();
        return $type;
    }

    private function createElasticSchema(): ElasticSchema
    {
        $elasticSchema = new ElasticSchema();
        $elasticSchema->setName('roundtrip-schema-' . uniqid());
        $elasticSchema->setSchema('{"type":"object","properties":{"title":{"type":"string"},"body":{"type":"string"}}}');
        $elasticSchema->setActive(true);
        $elasticSchema->save();
        return $elasticSchema;
    }

    private function createAuthor(): Author
    {
        $author = new Author();
        $author->setFirstname('Ada');
        $author->setLastname('Lovelace');
        $author->setActive(true);
        $author->save();
        return $author;
    }

    private function createSlug(string $path): Slug
    {
        $slug = new Slug();
        $slug->setHostId(1);
        $slug->setPath($path);
        $slug->setActive(true);
        $slug->save();
        return $slug;
    }

    private function createXeo(Slug $slug): Xeo
    {
        $xeo = new Xeo();
        $xeo->setSlugId($slug->getId());
        $xeo->setTitle('SEO title');
        $xeo->setActive(true);
        $xeo->save();
        return $xeo;
    }

    private function createSitemap(Slug $slug): Sitemap
    {
        $sitemap = new Sitemap();
        $sitemap->setSlugId($slug->getId());
        $sitemap->save();
        return $sitemap;
    }

    private function createTextBloc(ElasticSchema $elasticSchema): Bloc
    {
        $bloc = new Bloc();
        $bloc->setElasticSchemaId($elasticSchema->getId());
        $bloc->setActive(true);
        $bloc->title = 'Hello';
        $bloc->body = 'World';
        $bloc->save();
        return $bloc;
    }

    public function testContentRoundTrip(IntegrationTester $I): void
    {
        $I->wantTo('export a rich content, delete it, re-import it and prove the export round-trips');

        $this->bootServices();

        $language = $this->createLanguage();
        $type = $this->createType();
        $elasticSchema = $this->createElasticSchema();
        $author = $this->createAuthor();

        $slug = $this->createSlug('about-' . uniqid());
        $this->createXeo($slug);
        $this->createSitemap($slug);

        $parent = new Content();
        $parent->setName('Root');
        $parent->setLanguageId('fr');
        $parent->save();
        $parentPath = $parent->path;

        $content = new Content();
        $content->setName('About');
        $content->setLanguageId('fr');
        $content->setTypeId($type->getId());
        $content->setSlugId($slug->getId());
        $content->setActive(true);
        $content->saveInto($parent);

        $bloc = $this->createTextBloc($elasticSchema);
        $content->attachBloc($bloc);

        $tag = new Tag();
        $tag->setName('news-' . uniqid());
        $tag->save();
        $content->attachTag($tag);

        $contentAuthor = new ContentAuthor();
        $contentAuthor->setContentId($content->getId());
        $contentAuthor->setAuthorId($author->getId());
        $contentAuthor->setOrder(1);
        $contentAuthor->save();

        $export1 = $this->mapperService->export($content);

        $content->delete();

        $result = $this->importService->execute($export1, 'create', $parentPath, []);
        $I->assertTrue($result['success'] === true, 'Import failed: ' . ($result['error'] ?? ''));

        $content2 = $result['model'];
        $export2 = $this->mapperService->export($content2);

        $I->assertCount(1, $export1['authors'], 'source content should export one author');
        $I->assertCount(1, $export2['authors'], 're-imported content must keep its author');
        $I->assertSame($export1['authors'][0]['firstname'], $export2['authors'][0]['firstname']);

        $I->assertEquals(
            $this->stripVolatile($export1),
            $this->stripVolatile($export2)
        );
    }

    public function testTagRoundTrip(IntegrationTester $I): void
    {
        $I->wantTo('export a rich tag, delete it, re-import it and prove the export round-trips');

        $this->bootServices();

        $type = $this->createType();
        $elasticSchema = $this->createElasticSchema();
        $author = $this->createAuthor();

        $slug = $this->createSlug('topic-' . uniqid());
        $this->createXeo($slug);
        $this->createSitemap($slug);

        $parent = new Tag();
        $parent->setName('Root Tag');
        $parent->save();
        $parentPath = $parent->path;

        $tag = new Tag();
        $tag->setName('Climate');
        $tag->setTypeId($type->getId());
        $tag->setSlugId($slug->getId());
        $tag->setActive(true);
        $tag->saveInto($parent);

        $bloc = $this->createTextBloc($elasticSchema);
        $tag->attachBloc($bloc);

        $tagAuthor = new TagAuthor();
        $tagAuthor->setTagId($tag->getId());
        $tagAuthor->setAuthorId($author->getId());
        $tagAuthor->setOrder(1);
        $tagAuthor->save();

        $export1 = $this->mapperService->export($tag);

        $tag->delete();

        $result = $this->importService->execute($export1, 'create', $parentPath, []);
        $I->assertTrue($result['success'] === true, 'Import failed: ' . ($result['error'] ?? ''));

        $tag2 = $result['model'];
        $export2 = $this->mapperService->export($tag2);

        $I->assertCount(1, $export1['authors'], 'source tag should export one author');
        $I->assertCount(1, $export2['authors'], 're-imported tag must keep its author');
        $I->assertSame($export1['authors'][0]['firstname'], $export2['authors'][0]['firstname']);

        $I->assertEquals(
            $this->stripVolatile($export1),
            $this->stripVolatile($export2)
        );
    }

    public function testTranslatedContentExportDoesNotRecurse(IntegrationTester $I): void
    {
        $I->wantTo('export a translated content: the translations relation must not recurse');

        $this->bootServices();
        $this->createLanguage();

        $langEn = Language::query()->andWhere(['id' => 'en'])->one();
        if ($langEn === null) {
            $langEn = new Language();
            $langEn->setId('en');
            $langEn->setName('English');
            $langEn->setActive(true);
            $langEn->save();
        }

        $contentFr = new Content();
        $contentFr->setName('Bonjour');
        $contentFr->setLanguageId('fr');
        $contentFr->save();

        $contentEn = new Content();
        $contentEn->setName('Hello');
        $contentEn->setLanguageId('en');
        $contentEn->save();

        $contentFr->linkTranslation($contentEn);
        $contentFr->refresh();

        $export = $this->mapperService->export($contentFr);

        $I->assertArrayHasKey('translations', $export);
        $I->assertCount(1, $export['translations']);
        $I->assertEquals($contentEn->getId(), $export['translations'][0]['id']);
        $I->assertEquals('en', $export['translations'][0]['languageId']);
        $I->assertArrayNotHasKey('translations', $export['translations'][0]);
    }

    /**
     * Remove keys that legitimately change across a round-trip.
     *
     * Removed at every nesting level: 'id', 'dateCreate', 'dateUpdate' (database
     * surrogate keys and timestamps). Removed at the root level only:
     *  - 'path': the Hazeltree position of the element, reassigned on re-insert;
     *  - 'active': ImportService::createModel forces the new element inactive by
     *    design (imports land unpublished), so the root 'active' flag is volatile.
     * The nested 'path' inside 'slug' is the URL and the nested 'active' flags of
     * slug/xeo/sitemap are preserved by the import, so they are kept and compared.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function stripVolatile(array $data): array
    {
        $rootVolatileKeys = [
            'id',
            'dateCreate',
            'dateUpdate',
            'path',
            'active',
        ];
        $result = [];

        foreach ($data as $key => $value) {
            $skip = in_array($key, $rootVolatileKeys, true) === true;
            if ($skip === false) {
                $result[$key] = is_array($value) === true ? $this->stripNestedVolatile($value) : $value;
            }
        }

        return $result;
    }

    /**
     * Same as stripVolatile but keeps 'path' (URL of nested slug must match).
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function stripNestedVolatile(array $data): array
    {
        $nestedVolatileKeys = [
            'id',
            'dateCreate',
            'dateUpdate',
        ];
        $result = [];

        foreach ($data as $key => $value) {
            $skip = is_string($key) === true && in_array($key, $nestedVolatileKeys, true) === true;
            if ($skip === false) {
                $result[$key] = is_array($value) === true ? $this->stripNestedVolatile($value) : $value;
            }
        }

        return $result;
    }
}
