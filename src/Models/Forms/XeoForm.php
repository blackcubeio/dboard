<?php

declare(strict_types=1);

/**
 * XeoForm.php
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Dboard\Models\Forms;

use Blackcube\BridgeModel\Attributes\Bridge;
use Blackcube\BridgeModel\Attributes\IntOrNull;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\In;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Length;

/**
 * Xeo form model.
 * Manages XEO metadata for a Slug.
 */
final class XeoForm extends BridgeFormModel
{
    protected ?string $translateCategory = 'dboard-content';
    protected ?string $translateElasticCategory = 'dboard-content';

    protected ?int $xeoId = null;
    protected ?int $slugId = null;
    #[IntOrNull]
    protected ?int $canonicalSlugId = null;
    protected ?string $title = null;
    protected ?string $description = null;
    protected ?string $image = null;
    protected bool $noindex = false;
    protected bool $nofollow = false;
    protected bool $og = false;
    protected ?string $ogType = null;
    protected bool $twitter = false;
    protected ?string $twitterCard = null;
    protected string $jsonldType = 'WebPage';
    protected bool $speakable = false;
    protected ?string $keywords = null;
    protected bool $accessibleForFree = true;
    protected bool $active = false;
    protected bool $refresh = false;

    public function load(mixed $data, ?string $scope = null): bool
    {
        // Reset checkboxes before load (unchecked = not sent)
        $this->noindex = false;
        $this->nofollow = false;
        $this->og = false;
        $this->twitter = false;
        $this->speakable = false;
        $this->accessibleForFree = false;
        $this->active = false;
        $this->refresh = false;
        return parent::load($data, $scope);
    }

    #[Bridge]
    public function setXeoId(?int $xeoId): void
    {
        $this->xeoId = $xeoId;
    }
    #[Bridge]
    public function getXeoId(): ?int
    {
        return $this->xeoId;
    }
    #[Bridge]
    public function setSlugId(?int $slugId): void
    {
        $this->slugId = $slugId;
    }
    #[Bridge]
    public function getSlugId(): ?int
    {
        return $this->slugId;
    }
    #[Bridge]
    public function setCanonicalSlugId(?int $canonicalSlugId): void
    {
        $this->canonicalSlugId = $canonicalSlugId ?: null;
    }
    #[Bridge]
    public function getCanonicalSlugId(): ?int
    {
        return $this->canonicalSlugId;
    }
    #[Bridge]
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
    #[Bridge]
    public function getTitle(): ?string
    {
        return $this->title;
    }
    #[Bridge]
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
    #[Bridge]
    public function getDescription(): ?string
    {
        return $this->description;
    }
    #[Bridge]
    public function setImage(?string $image): void
    {
        $this->image = $image;
    }
    #[Bridge]
    public function getImage(): ?string
    {
        return $this->image;
    }
    #[Bridge]
    public function setNoindex(bool $noindex): void
    {
        $this->noindex = $noindex;
    }
    #[Bridge]
    public function isNoindex(): bool
    {
        return $this->noindex;
    }
    #[Bridge]
    public function setNofollow(bool $nofollow): void
    {
        $this->nofollow = $nofollow;
    }
    #[Bridge]
    public function isNofollow(): bool
    {
        return $this->nofollow;
    }
    #[Bridge]
    public function setOg(bool $og): void
    {
        $this->og = $og;
    }
    #[Bridge]
    public function isOg(): bool
    {
        return $this->og;
    }
    #[Bridge]
    public function setOgType(?string $ogType): void
    {
        $this->ogType = $ogType;
    }
    #[Bridge]
    public function getOgType(): ?string
    {
        return $this->ogType;
    }
    #[Bridge]
    public function setTwitter(bool $twitter): void
    {
        $this->twitter = $twitter;
    }
    #[Bridge]
    public function isTwitter(): bool
    {
        return $this->twitter;
    }
    #[Bridge]
    public function setTwitterCard(?string $twitterCard): void
    {
        $this->twitterCard = $twitterCard;
    }
    #[Bridge]
    public function getTwitterCard(): ?string
    {
        return $this->twitterCard;
    }
    #[Bridge]
    public function setJsonldType(string $jsonldType): void
    {
        $this->jsonldType = $jsonldType;
    }
    #[Bridge]
    public function getJsonldType(): string
    {
        return $this->jsonldType;
    }
    #[Bridge]
    public function setSpeakable(bool $speakable): void
    {
        $this->speakable = $speakable;
    }
    #[Bridge]
    public function isSpeakable(): bool
    {
        return $this->speakable;
    }
    #[Bridge]
    public function setKeywords(?string $keywords): void
    {
        $this->keywords = $keywords;
    }
    #[Bridge]
    public function getKeywords(): ?string
    {
        return $this->keywords;
    }
    #[Bridge]
    public function setAccessibleForFree(bool $accessibleForFree): void
    {
        $this->accessibleForFree = $accessibleForFree;
    }
    #[Bridge]
    public function isAccessibleForFree(): bool
    {
        return $this->accessibleForFree;
    }
    #[Bridge]
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
    #[Bridge]
    public function isActive(): bool
    {
        return $this->active;
    }

    public function setRefresh(bool $refresh): void
    {
        $this->refresh = $refresh;
    }

    public function isRefresh(): bool
    {
        return $this->refresh;
    }

    public function scenarios(): array
    {
        return [
            'create' => [
                'canonicalSlugId', 'title', 'description', 'image',
                'noindex', 'nofollow', 'og', 'ogType', 'twitter', 'twitterCard',
                'jsonldType', 'speakable', 'keywords', 'accessibleForFree', 'active',
                'refresh',
            ],
            'edit' => [
                'canonicalSlugId', 'title', 'description', 'image',
                'noindex', 'nofollow', 'og', 'ogType', 'twitter', 'twitterCard',
                'jsonldType', 'speakable', 'keywords', 'accessibleForFree', 'active',
                'refresh',
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'slugId' => [
                new Integer(min: 1),
            ],
            'canonicalSlugId' => [
                new Integer(skipOnEmpty: true),
            ],
            'title' => [
                new Length(max: 255),
            ],
            'description' => [
                new Length(max: 512),
            ],
            'image' => [
                new Length(max: 512),
            ],
            'noindex' => [
                new BooleanValue(),
            ],
            'nofollow' => [
                new BooleanValue(),
            ],
            'og' => [
                new BooleanValue(),
            ],
            'ogType' => [
                new In(['website', 'article', 'product', 'profile'], skipOnEmpty: true),
            ],
            'twitter' => [
                new BooleanValue(),
            ],
            'twitterCard' => [
                new In(['summary', 'summary_large_image', 'app', 'player'], skipOnEmpty: true),
            ],
            'jsonldType' => [
                new In(array_keys(self::getJsonldTypeOptions())),
            ],
            'speakable' => [
                new BooleanValue(),
            ],
            'keywords' => [
                new Length(max: 5000, skipOnEmpty: true),
            ],
            'accessibleForFree' => [
                new BooleanValue(),
            ],
            'active' => [
                new BooleanValue(),
            ],
            'refresh' => [
                new BooleanValue(skipOnEmpty: true),
            ],
        ];
    }

    protected function getRawLabels(): array
    {
        return [
            'canonicalSlugId' => 'Canonical URL',
            'title' => 'XEO title',
            'description' => 'Description',
            'image' => 'Image',
            'noindex' => 'Noindex',
            'nofollow' => 'Nofollow',
            'og' => 'Open Graph',
            'ogType' => 'OG type',
            'twitter' => 'Twitter Cards',
            'twitterCard' => 'Twitter type',
            'jsonldType' => 'JSON-LD type',
            'speakable' => 'Speakable content',
            'keywords' => 'Keywords',
            'accessibleForFree' => 'Free content',
            'active' => 'XEO active',
        ];
    }

    protected function getRawHints(): array
    {
        return [
            'title' => 'Title displayed in search results',
            'description' => 'Description displayed in search results',
            'image' => 'Image for social sharing',
            'noindex' => 'Prevent search engine indexing',
            'nofollow' => 'Prevent link following',
            'og' => 'Enable Open Graph tags (Facebook, LinkedIn...)',
            'ogType' => 'Open Graph content type',
            'twitter' => 'Enable Twitter Cards',
            'twitterCard' => 'Twitter card type',
            'jsonldType' => 'Schema.org page type (WebPage, Article, BlogPosting...)',
            'speakable' => 'Content suitable for voice reading by assistants (Google Assistant)',
            'keywords' => 'One keyword per line',
            'accessibleForFree' => 'Freely accessible content (uncheck for paid content)',
            'active' => 'Enable XEO tags',
        ];
    }

    /**
     * Get available OG types for dropdown.
     *
     * @return array<string, string>
     */
    public static function getOgTypeOptions(): array
    {
        return [
            '' => '-- None --',
            'website' => 'Website',
            'article' => 'Article',
            'product' => 'Product',
            'profile' => 'Profile',
        ];
    }

    /**
     * Get available Twitter card types for dropdown.
     *
     * @return array<string, string>
     */
    public static function getTwitterCardOptions(): array
    {
        return [
            '' => '-- None --',
            'summary' => 'Summary',
            'summary_large_image' => 'Summary Large Image',
            'app' => 'App',
            'player' => 'Player',
        ];
    }

    /**
     * Get available JSON-LD types for dropdown.
     *
     * @return array<string, string>
     */
    public static function getJsonldTypeOptions(): array
    {
        return [
            'WebPage' => 'Web page',
            'AboutPage' => 'About page',
            'ContactPage' => 'Contact page',
            'CollectionPage' => 'Collection / list page',
            'FAQPage' => 'FAQ page',
            'ItemPage' => 'Product page',
            'SearchResultsPage' => 'Search results page',
            'ProfilePage' => 'Profile page',
            'CheckoutPage' => 'Checkout page',
            'Article' => 'Article',
            'BlogPosting' => 'Blog post',
            'NewsArticle' => 'News article',
            'TechArticle' => 'Technical article',
            'ScholarlyArticle' => 'Scholarly article',
            'Report' => 'Report',
        ];
    }
}
