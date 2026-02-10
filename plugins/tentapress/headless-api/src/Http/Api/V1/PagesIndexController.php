<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Http\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use TentaPress\HeadlessApi\Support\ContentPayloadBuilder;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Seo\Models\TpSeoPage;

final class PagesIndexController
{
    public function __invoke(Request $request, ContentPayloadBuilder $content): JsonResponse
    {
        $perPage = max(1, min((int) $request->query('per_page', 12), 100));

        $query = TpPage::query()
            ->where('status', 'published')
            ->orderBy('slug');

        $slug = trim((string) $request->query('slug', ''));
        if ($slug !== '') {
            $query->where('slug', $slug);
        }

        $layout = trim((string) $request->query('layout', ''));
        if ($layout !== '') {
            $query->where('layout', $layout);
        }

        $pages = $query->paginate($perPage)->appends($request->query());
        $pageIds = $pages->getCollection()->pluck('id')->filter()->values();
        $seoByPageId = TpSeoPage::query()
            ->whereIn('page_id', $pageIds)
            ->get()
            ->keyBy('page_id');

        $data = $pages->getCollection()->map(function (TpPage $page) use ($content, $seoByPageId): array {
            $payload = $content->forPage($page);
            $seo = $seoByPageId->get((int) $page->id);

            return [
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
            ];
        })->values();

        return Response::json([
            'data' => $data,
            'meta' => [
                'current_page' => $pages->currentPage(),
                'per_page' => $pages->perPage(),
                'total' => $pages->total(),
                'last_page' => $pages->lastPage(),
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
