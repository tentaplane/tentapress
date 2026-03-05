<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Http\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
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
        $taxonomyTerms = $this->taxonomyTermsForPostId((int) $post->id);

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
                'taxonomies' => $taxonomyTerms,
                'seo' => $seoPayloadBuilder->forPost($seo),
                'updated_at' => $post->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * @return array<int,array{taxonomy_key:string,taxonomy_label:string,term_id:int,term_name:string,term_slug:string}>
     */
    private function taxonomyTermsForPostId(int $postId): array
    {
        if ($postId <= 0 || ! Schema::hasTable('tp_term_assignments') || ! Schema::hasTable('tp_terms') || ! Schema::hasTable('tp_taxonomies')) {
            return [];
        }

        return DB::table('tp_term_assignments')
            ->join('tp_terms', 'tp_terms.id', '=', 'tp_term_assignments.term_id')
            ->join('tp_taxonomies', 'tp_taxonomies.id', '=', 'tp_term_assignments.taxonomy_id')
            ->where('tp_term_assignments.assignable_type', TpPost::class)
            ->where('tp_term_assignments.assignable_id', $postId)
            ->orderBy('tp_taxonomies.label')
            ->orderBy('tp_terms.name')
            ->get([
                'tp_terms.id as term_id',
                'tp_terms.name as term_name',
                'tp_terms.slug as term_slug',
                'tp_taxonomies.key as taxonomy_key',
                'tp_taxonomies.label as taxonomy_label',
            ])
            ->map(static fn (object $row): array => [
                'taxonomy_key' => (string) ($row->taxonomy_key ?? ''),
                'taxonomy_label' => (string) ($row->taxonomy_label ?? ''),
                'term_id' => (int) ($row->term_id ?? 0),
                'term_name' => (string) ($row->term_name ?? ''),
                'term_slug' => (string) ($row->term_slug ?? ''),
            ])
            ->values()
            ->all();
    }

    private function nullableAuthorName(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }
}
