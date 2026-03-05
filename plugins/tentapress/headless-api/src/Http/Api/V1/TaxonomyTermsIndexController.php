<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Http\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use TentaPress\HeadlessApi\Support\ApiErrorResponder;

final class TaxonomyTermsIndexController
{
    public function __invoke(string $taxonomy, ApiErrorResponder $errors): JsonResponse
    {
        if (! Schema::hasTable('tp_taxonomies') || ! Schema::hasTable('tp_terms')) {
            return $errors->notFound('Taxonomy not found');
        }

        $taxonomyRow = DB::table('tp_taxonomies')
            ->where('key', $taxonomy)
            ->where('is_public', true)
            ->first(['id', 'key', 'label', 'singular_label', 'description', 'is_hierarchical', 'is_public']);

        if (! is_object($taxonomyRow)) {
            return $errors->notFound('Taxonomy not found');
        }

        $terms = DB::table('tp_terms')
            ->where('taxonomy_id', (int) $taxonomyRow->id)
            ->orderBy('name')
            ->get(['id', 'taxonomy_id', 'parent_id', 'name', 'slug', 'description'])
            ->map(static fn (object $term): array => [
                'id' => (int) ($term->id ?? 0),
                'taxonomy_id' => (int) ($term->taxonomy_id ?? 0),
                'parent_id' => is_numeric($term->parent_id ?? null) ? (int) $term->parent_id : null,
                'name' => (string) ($term->name ?? ''),
                'slug' => (string) ($term->slug ?? ''),
                'description' => trim((string) ($term->description ?? '')) ?: null,
            ])
            ->values()
            ->all();

        return Response::json([
            'data' => [
                'taxonomy' => [
                    'id' => (int) ($taxonomyRow->id ?? 0),
                    'key' => (string) ($taxonomyRow->key ?? ''),
                    'label' => (string) ($taxonomyRow->label ?? ''),
                    'singular_label' => (string) ($taxonomyRow->singular_label ?? ''),
                    'description' => trim((string) ($taxonomyRow->description ?? '')) ?: null,
                    'is_hierarchical' => (bool) ($taxonomyRow->is_hierarchical ?? false),
                    'is_public' => (bool) ($taxonomyRow->is_public ?? false),
                ],
                'terms' => $terms,
            ],
        ]);
    }
}
