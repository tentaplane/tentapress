<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Http\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use TentaPress\HeadlessApi\Support\ContentPayloadBuilder;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Settings\Services\SettingsStore;
use TentaPress\Seo\Models\TpSeoPost;

final class PostsIndexController
{
    public function __invoke(Request $request, ContentPayloadBuilder $content, SettingsStore $settings): JsonResponse
    {
        $perPage = max(1, min((int) $request->query('per_page', 12), 100));
        $blogBase = $this->blogBase($settings);

        $query = TpPost::query()
            ->with('author')
            ->where('status', 'published')
            ->where(function ($inner): void {
                $inner->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->latest('published_at')
            ->latest('id');

        $author = trim((string) $request->query('author', ''));
        if ($author !== '' && ctype_digit($author)) {
            $query->where('author_id', (int) $author);
        }

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($inner) use ($search): void {
                $inner->whereLike('title', '%'.$search.'%')
                    ->orWhereLike('slug', '%'.$search.'%');
            });
        }

        $posts = $query->paginate($perPage)->appends($request->query());
        $postIds = $posts->getCollection()->pluck('id')->filter()->values();
        $seoByPostId = TpSeoPost::query()
            ->whereIn('post_id', $postIds)
            ->get()
            ->keyBy('post_id');

        $data = $posts->getCollection()->map(function (TpPost $post) use ($blogBase, $content, $seoByPostId): array {
            $payload = $content->forPost($post);
            $seo = $seoByPostId->get((int) $post->id);

            return [
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
            ];
        })->values();

        return Response::json([
            'data' => $data,
            'meta' => [
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'last_page' => $posts->lastPage(),
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
