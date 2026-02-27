<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Http\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use TentaPress\HeadlessApi\Support\BlogBaseResolver;
use TentaPress\HeadlessApi\Support\ContentPayloadBuilder;
use TentaPress\HeadlessApi\Support\SeoPayloadBuilder;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Settings\Services\SettingsStore;
use TentaPress\Seo\Models\TpSeoPost;

final class PostsIndexController
{
    public function __invoke(
        Request $request,
        ContentPayloadBuilder $content,
        SettingsStore $settings,
        BlogBaseResolver $blogBaseResolver,
        SeoPayloadBuilder $seoPayloadBuilder,
    ): JsonResponse {
        $perPage = max(1, min((int) $request->query('per_page', 12), 100));
        $blogBase = $blogBaseResolver->fromSettings($settings);

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

        $data = $posts->getCollection()->map(function (TpPost $post) use ($blogBase, $content, $seoByPostId, $seoPayloadBuilder): array {
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
                    'name' => $this->nullableAuthorName($post->author?->name),
                ],
                'content_raw' => $payload['content_raw'],
                'content_html' => $payload['content_html'],
                'seo' => $seoPayloadBuilder->forPost($seo),
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

    private function nullableAuthorName(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }
}
