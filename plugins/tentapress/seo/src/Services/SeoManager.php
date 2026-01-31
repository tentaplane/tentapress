<?php

declare(strict_types=1);

namespace TentaPress\Seo\Services;

use Illuminate\Support\Facades\Request;
use TentaPress\Seo\Models\TpSeoPage;
use TentaPress\Seo\Models\TpSeoPost;

final readonly class SeoManager
{
    public function __construct(
        private SeoSettings $settings,
    ) {
    }

    /**
     * @param object $page expects ->id, ->title, ->slug (TpPage)
     * @return array<string,string>
     */
    public function forPage(object $page): array
    {
        $row = null;

        if (isset($page->id)) {
            $row = TpSeoPage::query()->where('page_id', (int) $page->id)->first();
        }

        $pageTitle = isset($page->title) ? (string) $page->title : '';
        $slug = isset($page->slug) ? (string) $page->slug : '';

        return $this->buildMeta($row, $pageTitle, $slug);
    }

    /**
     * @param object $post expects ->id, ->title, ->slug (TpPost)
     * @return array<string,string>
     */
    public function forPost(object $post): array
    {
        $row = null;

        if (isset($post->id)) {
            $row = TpSeoPost::query()->where('post_id', (int) $post->id)->first();
        }

        $postTitle = isset($post->title) ? (string) $post->title : '';
        $slug = isset($post->slug) ? (string) $post->slug : '';

        return $this->buildMeta($row, $postTitle, $slug);
    }

    /**
     * @return array<string,string>
     */
    public function forBlogIndex(): array
    {
        $title = $this->settings->blogTitle();
        $description = $this->settings->blogDescription();
        $base = $this->settings->blogBase();

        $row = (object) [
            'title' => $title,
            'description' => $description,
        ];

        $fallbackTitle = trim($title) !== '' ? $title : 'Blog';

        return $this->buildMeta($row, $fallbackTitle, $base);
    }

    /**
     * @param object|null $row expects ->title, ->description, ->robots, ->canonical_url, ->og_*, ->twitter_*
     * @return array<string,string>
     */
    private function buildMeta(?object $row, string $title, string $slug): array
    {
        $siteTitle = (string) $this->settings->get('site.title', '');

        $resolvedTitle = $this->resolveTitle($row?->title ?? null, $title, $siteTitle);
        $description = $this->resolveDescription($row?->description ?? null);
        $robots = $this->resolveRobots($row?->robots ?? null);
        $canonical = $this->resolveCanonical($row?->canonical_url ?? null, $slug);

        $ogTitle = $this->fallback((string) ($row?->og_title ?? ''), $resolvedTitle);
        $ogDescription = $this->fallback((string) ($row?->og_description ?? ''), $description);
        $ogImage = trim((string) ($row?->og_image ?? ''));

        $twTitle = $this->fallback((string) ($row?->twitter_title ?? ''), $ogTitle);
        $twDescription = $this->fallback((string) ($row?->twitter_description ?? ''), $ogDescription);
        $twImage = $this->fallback((string) ($row?->twitter_image ?? ''), $ogImage);

        $meta = [
            'title' => $resolvedTitle,
            'description' => $description,
            'robots' => $robots,
            'canonical' => $canonical,
            'og:title' => $ogTitle,
            'og:description' => $ogDescription,
            'og:image' => $ogImage,
            'twitter:title' => $twTitle,
            'twitter:description' => $twDescription,
            'twitter:image' => $twImage,
        ];

        return array_filter($meta, fn ($v) => is_string($v) && trim($v) !== '');
    }

    private function resolveTitle(?string $override, string $pageTitle, string $siteTitle): string
    {
        $override = trim((string) ($override ?? ''));
        if ($override !== '') {
            return $override;
        }

        $template = $this->normalizeTitleTemplate((string) $this->settings->titleTemplate());

        $out = str_replace(
            ['{{page_title}}', '{{site_title}}'],
            [$pageTitle, $siteTitle],
            $template
        );

        $out = trim(preg_replace('/\s+/', ' ', $out) ?? $out);

        return $out !== '' ? $out : $pageTitle;
    }

    private function normalizeTitleTemplate(string $template): string
    {
        $template = preg_replace('/{{\s*\$page->title\s*}}/', '{{page_title}}', $template) ?? $template;
        $template = preg_replace('/{{\s*\$tpSiteTitle\s*}}/', '{{site_title}}', $template) ?? $template;

        return $template;
    }

    private function resolveDescription(?string $override): string
    {
        $override = trim((string) ($override ?? ''));
        if ($override !== '') {
            return $override;
        }

        return trim((string) $this->settings->defaultDescription());
    }

    private function resolveRobots(?string $override): string
    {
        $override = trim((string) ($override ?? ''));
        if ($override !== '') {
            return $override;
        }

        $d = trim((string) $this->settings->defaultRobots());
        return $d !== '' ? $d : 'index,follow';
    }

    private function resolveCanonical(?string $override, string $slug): string
    {
        $override = trim((string) ($override ?? ''));
        if ($override !== '') {
            return $override;
        }

        $base = trim((string) $this->settings->canonicalBase());
        if ($base !== '' && $slug !== '') {
            return rtrim($base, '/') . '/' . ltrim($slug, '/');
        }

        // Fallback to current URL
        return (string) Request::fullUrl();
    }

    private function fallback(string $value, string $fallback): string
    {
        $value = trim($value);
        return $value !== '' ? $value : $fallback;
    }
}
