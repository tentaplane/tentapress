<?php

declare(strict_types=1);

namespace TentaPress\HeadlessApi\Http\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;

final class TaxonomiesIndexController
{
    public function __invoke(): JsonResponse
    {
        if (! Schema::hasTable('tp_taxonomies') || ! Schema::hasTable('tp_terms')) {
            return Response::json([
                'data' => [],
            ]);
        }

        $taxonomies = DB::table('tp_taxonomies')
            ->where('is_public', true)
            ->orderBy('label')
            ->get([
                'id',
                'key',
                'label',
                'singular_label',
                'description',
                'is_hierarchical',
                'is_public',
            ]);

        $taxonomyIds = $taxonomies->pluck('id')->map(static fn (mixed $value): int => (int) $value)->all();
        $termsByTaxonomy = DB::table('tp_terms')
            ->whereIn('taxonomy_id', $taxonomyIds)
            ->orderBy('taxonomy_id')
            ->orderBy('name')
            ->get(['id', 'taxonomy_id', 'parent_id', 'name', 'slug', 'description'])
            ->groupBy('taxonomy_id');

        $data = $taxonomies->map(function (object $taxonomy) use ($termsByTaxonomy): array {
            $terms = $termsByTaxonomy->get($taxonomy->id);

            return [
                'id' => (int) ($taxonomy->id ?? 0),
                'key' => (string) ($taxonomy->key ?? ''),
                'label' => (string) ($taxonomy->label ?? ''),
                'singular_label' => (string) ($taxonomy->singular_label ?? ''),
                'description' => trim((string) ($taxonomy->description ?? '')) ?: null,
                'is_hierarchical' => (bool) ($taxonomy->is_hierarchical ?? false),
                'is_public' => (bool) ($taxonomy->is_public ?? false),
                'terms' => ($terms ?? collect())->map(static fn (object $term): array => [
                    'id' => (int) ($term->id ?? 0),
                    'taxonomy_id' => (int) ($term->taxonomy_id ?? 0),
                    'parent_id' => is_numeric($term->parent_id ?? null) ? (int) $term->parent_id : null,
                    'name' => (string) ($term->name ?? ''),
                    'slug' => (string) ($term->slug ?? ''),
                    'description' => trim((string) ($term->description ?? '')) ?: null,
                ])->values()->all(),
            ];
        })->values();

        return Response::json([
            'data' => $data,
        ]);
    }
}
