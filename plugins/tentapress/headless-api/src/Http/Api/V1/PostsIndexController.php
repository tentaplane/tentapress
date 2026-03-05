<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Http\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
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

        $taxonomyKey = trim((string) $request->query('taxonomy', ''));
        $termSlug = trim((string) $request->query('term', ''));

        if (($taxonomyKey !== '' || $termSlug !== '') && Schema::hasTable('tp_taxonomies') && Schema::hasTable('tp_terms') && Schema::hasTable('tp_term_assignments')) {
            $query->whereExists(function ($innerQuery) use ($taxonomyKey, $termSlug): void {
                $innerQuery->selectRaw('1')
                    ->from('tp_term_assignments')
                    ->join('tp_terms', 'tp_terms.id', '=', 'tp_term_assignments.term_id')
                    ->join('tp_taxonomies', 'tp_taxonomies.id', '=', 'tp_terms.taxonomy_id')
                    ->whereColumn('tp_term_assignments.assignable_id', 'tp_posts.id')
                    ->where('tp_term_assignments.assignable_type', TpPost::class)
                    ->when($taxonomyKey !== '', static fn ($filter) => $filter->where('tp_taxonomies.key', $taxonomyKey))
                    ->when($termSlug !== '', static fn ($filter) => $filter->where('tp_terms.slug', $termSlug));
            });
        }

        $posts = $query->paginate($perPage)->appends($request->query());
        $postIds = $posts->getCollection()->pluck('id')->filter()->values();
        $seoByPostId = TpSeoPost::query()
            ->whereIn('post_id', $postIds)
            ->get()
            ->keyBy('post_id');
        $taxonomiesByPostId = $this->taxonomyTermsForPostIds($postIds->all());

        $data = $posts->getCollection()->map(function (TpPost $post) use ($blogBase, $content, $seoByPostId, $seoPayloadBuilder, $taxonomiesByPostId): array {
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
                'taxonomies' => $taxonomiesByPostId[(int) $post->id] ?? [],
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
                'filters' => [
                    'author' => $author !== '' && ctype_digit($author) ? (int) $author : null,
                    'q' => $search !== '' ? $search : null,
                    'taxonomy' => $taxonomyKey !== '' ? $taxonomyKey : null,
                    'term' => $termSlug !== '' ? $termSlug : null,
                ],
            ],
        ]);
    }

    /**
     * @param array<int,mixed> $postIds
     * @return array<int,array<int,array{taxonomy_key:string,taxonomy_label:string,term_id:int,term_name:string,term_slug:string}>>
     */
    private function taxonomyTermsForPostIds(array $postIds): array
    {
        $normalizedPostIds = array_values(array_filter(array_map(static fn (mixed $value): int => (int) $value, $postIds), static fn (int $value): bool => $value > 0));
        if ($normalizedPostIds === [] || ! Schema::hasTable('tp_term_assignments') || ! Schema::hasTable('tp_terms') || ! Schema::hasTable('tp_taxonomies')) {
            return [];
        }

        $rows = DB::table('tp_term_assignments')
            ->join('tp_terms', 'tp_terms.id', '=', 'tp_term_assignments.term_id')
            ->join('tp_taxonomies', 'tp_taxonomies.id', '=', 'tp_term_assignments.taxonomy_id')
            ->where('tp_term_assignments.assignable_type', TpPost::class)
            ->whereIn('tp_term_assignments.assignable_id', $normalizedPostIds)
            ->orderBy('tp_term_assignments.assignable_id')
            ->orderBy('tp_taxonomies.label')
            ->orderBy('tp_terms.name')
            ->get([
                'tp_term_assignments.assignable_id as assignable_id',
                'tp_terms.id as term_id',
                'tp_terms.name as term_name',
                'tp_terms.slug as term_slug',
                'tp_taxonomies.key as taxonomy_key',
                'tp_taxonomies.label as taxonomy_label',
            ]);

        $grouped = [];
        foreach ($rows as $row) {
            $postId = (int) ($row->assignable_id ?? 0);
            if ($postId <= 0) {
                continue;
            }

            $grouped[$postId] ??= [];
            $grouped[$postId][] = [
                'taxonomy_key' => (string) ($row->taxonomy_key ?? ''),
                'taxonomy_label' => (string) ($row->taxonomy_label ?? ''),
                'term_id' => (int) ($row->term_id ?? 0),
                'term_name' => (string) ($row->term_name ?? ''),
                'term_slug' => (string) ($row->term_slug ?? ''),
            ];
        }

        return $grouped;
    }

    private function nullableAuthorName(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }
}
