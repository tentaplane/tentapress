<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Http\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use TentaPress\HeadlessApi\Support\ContentPayloadBuilder;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Settings\Services\SettingsStore;
use TentaPress\Seo\Models\TpSeoPost;

final class PostShowController
{
    public function __invoke(string $slug, ContentPayloadBuilder $content, SettingsStore $settings): JsonResponse
    {
        $blogBase = $this->blogBase($settings);
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
            return Response::json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'Post not found',
                ],
            ], 404);
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
                    'name' => $this->nullableString($post->author?->name),
                ],
                'content_raw' => $payload['content_raw'],
                'content_html' => $payload['content_html'],
                'seo' => $this->seoPayload($seo),
                'updated_at' => $post->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * @return array<string,mixed>|null
     */
    private function seoPayload(?TpSeoPost $seo): ?array
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

    private function blogBase(SettingsStore $settings): string
    {
        $blogBase = trim((string) $settings->get('site.blog_base', 'blog'), '/');

        if ($blogBase !== '' && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $blogBase) === 1) {
            return $blogBase;
        }

        return 'blog';
    }
}
