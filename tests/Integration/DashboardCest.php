<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Tests\Integration;

use Blackcube\Dboard\DataServices\DashboardDataService;
use Blackcube\Dboard\Services\DashboardService;
use Blackcube\Dboard\Tests\Support\DatabaseCestTrait;
use Blackcube\Dboard\Tests\Support\IntegrationTester;
use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Models\Slug;
use Blackcube\Dcore\Models\Type;
use DateTimeImmutable;

/**
 * The numbers of the dashboard against the database: each counter moves by
 * exactly what is written.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class DashboardCest
{
    use DatabaseCestTrait;

    public function testTheContentCountersFollowTheRecords(IntegrationTester $I): void
    {
        $I->wantTo('verify the content counters move by what is written');

        $dashboardService = new DashboardService(new DashboardDataService());
        $now = new DateTimeImmutable();
        $activeBefore = $dashboardService->getCountActive();
        $scheduledBefore = $dashboardService->getCountScheduled();
        $expiredBefore = $dashboardService->getCountExpired();
        $inactiveBefore = $dashboardService->getCountInactive();
        $unreachableBefore = $dashboardService->getCountUnreachable();
        $missingBefore = $dashboardService->getCountMissingTranslations();
        $updatedBefore = $dashboardService->getCountUpdatedWithin(7);

        $type = new Type();
        $type->setName('Page');
        $type->save();

        $this->createContent('Published', true, null, null, null);
        $this->createContent('Scheduled', true, $now->modify('+1 day'), null, null);
        $this->createContent('Expired', true, null, $now->modify('-1 day'), null);
        $this->createContent('Draft', false, null, null, null);
        $this->createContent('Unreachable', true, null, null, $type->getId());

        $I->assertSame($activeBefore + 2, $dashboardService->getCountActive(), 'published and unreachable are active inside their window');
        $I->assertSame($scheduledBefore + 1, $dashboardService->getCountScheduled());
        $I->assertSame($expiredBefore + 1, $dashboardService->getCountExpired());
        $I->assertSame($inactiveBefore + 1, $dashboardService->getCountInactive());
        $I->assertSame($unreachableBefore + 1, $dashboardService->getCountUnreachable());
        $I->assertSame($missingBefore + 4, $dashboardService->getCountMissingTranslations(), 'the four active contents have no translation group');
        $I->assertSame($updatedBefore + 5, $dashboardService->getCountUpdatedWithin(7), 'dateUpdate is set on insert by the model');
    }

    public function testTheSiteHealthFollowsTheSlugs(IntegrationTester $I): void
    {
        $I->wantTo('verify the site health counters move by the slugs written');

        $dashboardService = new DashboardService(new DashboardDataService());
        $withoutXeoBefore = $dashboardService->getCountPagesWithoutXeo();
        $outOfSitemapBefore = $dashboardService->getCountPagesOutOfSitemap();
        $redirectionsBefore = $dashboardService->getCountRedirections();
        $inactiveBefore = $dashboardService->getCountInactiveSlugs();

        $this->createSlug('a-page', true, null);
        $this->createSlug('old-page', true, 'https://example.com/new-page');
        $this->createSlug('retired-page', false, null);

        $I->assertSame($withoutXeoBefore + 1, $dashboardService->getCountPagesWithoutXeo(), 'the page alone: a redirection and a retired slug are not pages');
        $I->assertSame($outOfSitemapBefore + 1, $dashboardService->getCountPagesOutOfSitemap());
        $I->assertSame($redirectionsBefore + 1, $dashboardService->getCountRedirections());
        $I->assertSame($inactiveBefore + 1, $dashboardService->getCountInactiveSlugs());
    }

    public function testTheShapeCountsTheActiveContents(IntegrationTester $I): void
    {
        $I->wantTo('verify the shape counts the active contents by language and by type');

        $dashboardService = new DashboardService(new DashboardDataService());
        $languageBefore = $this->totalsByName($dashboardService->getCountsByLanguage());
        $typeBefore = $this->totalsByName($dashboardService->getCountsByType());

        $type = new Type();
        $type->setName('Article');
        $type->save();
        $this->createContent('First article', true, null, null, $type->getId(), 'fr');
        $this->createContent('Second article', true, null, null, $type->getId(), 'fr');
        $this->createContent('Untyped', true, null, null, null, 'en');
        $this->createContent('Draft article', false, null, null, $type->getId(), 'fr');

        /** @var Language $french */
        $french = Language::query()->andWhere([
            'id' => 'fr',
        ])->one();
        /** @var Language $english */
        $english = Language::query()->andWhere([
            'id' => 'en',
        ])->one();
        $languageAfter = $this->totalsByName($dashboardService->getCountsByLanguage());
        $typeAfter = $this->totalsByName($dashboardService->getCountsByType());

        $I->assertSame(($languageBefore[$french->getName()] ?? 0) + 2, $languageAfter[$french->getName()], 'the draft is not counted');
        $I->assertSame(($languageBefore[$english->getName()] ?? 0) + 1, $languageAfter[$english->getName()]);
        $I->assertSame(($typeBefore['Article'] ?? 0) + 2, $typeAfter['Article']);
        $I->assertSame(($typeBefore[''] ?? 0) + 1, $typeAfter[''], 'the contents without a type come with a null name');
    }

    private function createContent(string $name, bool $active, ?DateTimeImmutable $dateStart, ?DateTimeImmutable $dateEnd, ?int $typeId, string $languageId = 'fr'): Content
    {
        $content = new Content();
        $content->setName($name);
        $content->setLanguageId($languageId);
        $content->setActive($active);
        $content->setDateStart($dateStart);
        $content->setDateEnd($dateEnd);
        $content->setTypeId($typeId);
        $content->save();
        return $content;
    }

    private function createSlug(string $path, bool $active, ?string $targetUrl): Slug
    {
        $slug = new Slug();
        $slug->setPath($path);
        $slug->setActive($active);
        $slug->setTargetUrl($targetUrl);
        if ($targetUrl !== null) {
            $slug->setHttpCode(301);
        }
        $slug->save();
        return $slug;
    }

    /**
     * @param array<int, array{name: string|null, total: int}> $counts
     * @return array<string, int>
     */
    private function totalsByName(array $counts): array
    {
        $totals = [];
        foreach ($counts as $count) {
            $totals[(string) $count['name']] = $count['total'];
        }
        return $totals;
    }
}
