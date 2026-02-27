<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Http\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use TentaPress\HeadlessApi\Support\ApiErrorResponder;
use TentaPress\HeadlessApi\Support\BlogBaseResolver;
use TentaPress\HeadlessApi\Support\ContentPayloadBuilder;
use TentaPress\HeadlessApi\Support\SeoPayloadBuilder;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Settings\Services\SettingsStore;
use TentaPress\Seo\Models\TpSeoPost;

final class PostShowController
{
    public function __invoke(
        string $slug,
        ContentPayloadBuilder $content,
        SettingsStore $settings,
        BlogBaseResolver $blogBaseResolver,
        SeoPayloadBuilder $seoPayloadBuilder,
        ApiErrorResponder $errors,
    ): JsonResponse {
        $blogBase = $blogBaseResolver->fromSettings($settings);
        $post = TpPost::query()
            ->with('author')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->first();

        if (! $post) {
            return $errors->notFound('Post not found');
        }

        $payload = $content->forPost($post);
        $seo = TpSeoPost::query()->where('post_id', (int) $post->id)->first();

        return Response::json([
            'data' => [
                'id' => (int) $post->id,
                'type' => 'post',
                'title' => (string) ($post->title ?? ''),
                'slug' => (string) ($post->slug ?? ''),
                'status' => (string) ($post->status ?? ''),
                'layout' => (string) ($post->layout ?? ''),
                'editor_driver' => (string) ($post->editor_driver ?? 'blocks'),
                'published_at' => $post->published_at?->toIso8601String(),
                'permalink' => '/'.$blogBase.'/'.ltrim((string) ($post->slug ?? ''), '/'),
                'author' => [
                    'id' => is_numeric($post->author_id) ? (int) $post->author_id : null,
                    'name' => $this->nullableAuthorName($post->author?->name),
                ],
                'content_raw' => $payload['content_raw'],
                'content_html' => $payload['content_html'],
                'seo' => $seoPayloadBuilder->forPost($seo),
                'updated_at' => $post->updated_at?->toIso8601String(),
            ],
        ]);
    }

    private function nullableAuthorName(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }
}
