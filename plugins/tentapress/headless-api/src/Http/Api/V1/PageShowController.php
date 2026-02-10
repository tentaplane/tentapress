<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Http\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use TentaPress\HeadlessApi\Support\ContentPayloadBuilder;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Seo\Models\TpSeoPage;

final class PageShowController
{
    public function __invoke(string $slug, ContentPayloadBuilder $content): JsonResponse
    {
        $page = TpPage::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (! $page) {
            return Response::json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'Page not found',
                ],
            ], 404);
        }

        $payload = $content->forPage($page);
        $seo = TpSeoPage::query()->where('page_id', (int) $page->id)->first();

        return Response::json([
            'data' => [
                'id' => (int) $page->id,
                'type' => 'page',
                'title' => (string) ($page->title ?? ''),
                'slug' => (string) ($page->slug ?? ''),
                'status' => (string) ($page->status ?? ''),
                'layout' => (string) ($page->layout ?? ''),
                'editor_driver' => (string) ($page->editor_driver ?? 'blocks'),
                'published_at' => $page->published_at?->toIso8601String(),
                'permalink' => '/'.ltrim((string) ($page->slug ?? ''), '/'),
                'content_raw' => $payload['content_raw'],
                'content_html' => $payload['content_html'],
                'seo' => $this->seoPayload($seo),
                'updated_at' => $page->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * @return array<string,mixed>|null
     */
    private function seoPayload(?TpSeoPage $seo): ?array
    {
        if (! $seo) {
            return null;
        }

        return [
            'title' => $this->nullableString($seo->title),
            'description' => $this->nullableString($seo->description),
            'canonical_url' => $this->nullableString($seo->canonical_url),
            'robots' => $this->nullableString($seo->robots),
            'og_title' => $this->nullableString($seo->og_title),
            'og_description' => $this->nullableString($seo->og_description),
            'og_image' => $this->nullableString($seo->og_image),
            'twitter_title' => $this->nullableString($seo->twitter_title),
            'twitter_description' => $this->nullableString($seo->twitter_description),
            'twitter_image' => $this->nullableString($seo->twitter_image),
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }
}
