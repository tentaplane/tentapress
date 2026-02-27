<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Http\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use TentaPress\HeadlessApi\Support\ApiErrorResponder;
use TentaPress\HeadlessApi\Support\ContentPayloadBuilder;
use TentaPress\HeadlessApi\Support\SeoPayloadBuilder;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Seo\Models\TpSeoPage;

final class PageShowController
{
    public function __invoke(
        string $slug,
        ContentPayloadBuilder $content,
        SeoPayloadBuilder $seoPayloadBuilder,
        ApiErrorResponder $errors,
    ): JsonResponse {
        $page = TpPage::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (! $page) {
            return $errors->notFound('Page not found');
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
                'seo' => $seoPayloadBuilder->forPage($seo),
                'updated_at' => $page->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
