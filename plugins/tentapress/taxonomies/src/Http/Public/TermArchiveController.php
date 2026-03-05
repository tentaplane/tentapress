<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Http\Public;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Taxonomies\Models\TpTaxonomy;
use TentaPress\Taxonomies\Models\TpTerm;

final class TermArchiveController
{
    public function __invoke(Request $request, string $taxonomy, string $term)
    {
        $taxonomyRecord = TpTaxonomy::query()
            ->where('key', $taxonomy)
            ->where('is_public', true)
            ->first();

        abort_unless($taxonomyRecord?->exists, 404);

        $termRecord = TpTerm::query()
            ->where('taxonomy_id', $taxonomyRecord->id)
            ->where('slug', $term)
            ->first();

        abort_unless($termRecord?->exists, 404);

        $posts = $this->resolvePostsPaginator($request, $termRecord);
        $view = $this->resolveArchiveView((string) $taxonomyRecord->key);

        return view($view, [
            'taxonomy' => $taxonomyRecord,
            'term' => $termRecord,
            'posts' => $posts,
            'archive' => [
                'taxonomy_key' => (string) $taxonomyRecord->key,
                'taxonomy_label' => (string) $taxonomyRecord->label,
                'term_slug' => (string) $termRecord->slug,
                'term_name' => (string) $termRecord->name,
            ],
        ]);
    }

    private function resolveArchiveView(string $taxonomyKey): string
    {
        $candidates = [
            'tp-theme::taxonomies.term-'.$taxonomyKey,
            'tp-theme::taxonomies.term',
            'tp-theme::taxonomy.term',
            'tentapress-taxonomies::public.term',
        ];

        foreach ($candidates as $candidate) {
            if (View::exists($candidate)) {
                return $candidate;
            }
        }

        return 'tentapress-taxonomies::public.term';
    }

    private function resolvePostsPaginator(Request $request, TpTerm $term): LengthAwarePaginator
    {
        $perPage = 12;

        if (! class_exists(TpPost::class) || ! Schema::hasTable('tp_posts') || ! Schema::hasTable('tp_term_assignments')) {
            return new Paginator([], 0, $perPage, Paginator::resolveCurrentPage(), [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        return TpPost::query()
            ->with('author')
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->whereExists(function ($query) use ($term): void {
                $query->selectRaw('1')
                    ->from('tp_term_assignments')
                    ->whereColumn('tp_term_assignments.assignable_id', 'tp_posts.id')
                    ->where('tp_term_assignments.assignable_type', TpPost::class)
                    ->where('tp_term_assignments.term_id', (int) $term->id);
            })
            ->latest('published_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }
}
