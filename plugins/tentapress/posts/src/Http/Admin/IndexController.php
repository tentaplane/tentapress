<?php

declare(strict_types=1);

namespace TentaPress\Posts\Http\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use TentaPress\Posts\Models\TpPost;

final class IndexController
{
    public function __invoke(Request $request)
    {
        $status = (string) $request->query('status', 'all');
        $search = trim((string) $request->query('s', ''));
        $sort = (string) $request->query('sort', 'title');
        $requestedDirection = strtolower((string) $request->query('direction', 'asc'));
        $taxonomyTermId = max(0, (int) $request->query('taxonomy_term', 0));

        $sortColumns = [
            'title' => 'title',
            'slug' => 'slug',
            'status' => 'status',
            'published' => 'published_at',
            'updated' => 'updated_at',
        ];
        $sort = array_key_exists($sort, $sortColumns) ? $sort : 'updated';

        $defaultDirection = in_array($sort, ['title', 'slug', 'status'], true) ? 'asc' : 'desc';
        $direction = in_array($requestedDirection, ['asc', 'desc'], true) ? $requestedDirection : $defaultDirection;

        $query = TpPost::query()
            ->orderBy($sortColumns[$sort], $direction)
            ->orderBy('id');

        if ($status === 'draft' || $status === 'published') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($qq) use ($search): void {
                $qq->whereLike('title', '%'.$search.'%')
                    ->orWhereLike('slug', '%'.$search.'%');
            });
        }

        if ($taxonomyTermId > 0 && Schema::hasTable('tp_term_assignments')) {
            $query->whereExists(function ($termQuery) use ($taxonomyTermId): void {
                $termQuery->selectRaw('1')
                    ->from('tp_term_assignments')
                    ->whereColumn('tp_term_assignments.assignable_id', 'tp_posts.id')
                    ->where('tp_term_assignments.assignable_type', TpPost::class)
                    ->where('tp_term_assignments.term_id', $taxonomyTermId);
            });
        }

        $posts = $query->paginate(20)->withQueryString();

        return view('tentapress-posts::posts.index', [
            'posts' => $posts,
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'taxonomyTermId' => $taxonomyTermId,
            'taxonomyTermOptions' => $this->taxonomyTermOptions(),
        ]);
    }

    /**
     * @return array<int,array{term_id:int,taxonomy_label:string,term_name:string}>
     */
    private function taxonomyTermOptions(): array
    {
        if (! Schema::hasTable('tp_taxonomies') || ! Schema::hasTable('tp_terms')) {
            return [];
        }

        return DB::table('tp_terms')
            ->join('tp_taxonomies', 'tp_taxonomies.id', '=', 'tp_terms.taxonomy_id')
            ->orderBy('tp_taxonomies.label')
            ->orderBy('tp_terms.name')
            ->get([
                'tp_terms.id as term_id',
                'tp_taxonomies.label as taxonomy_label',
                'tp_terms.name as term_name',
            ])
            ->map(static fn (object $row): array => [
                'term_id' => (int) ($row->term_id ?? 0),
                'taxonomy_label' => trim((string) ($row->taxonomy_label ?? '')),
                'term_name' => trim((string) ($row->term_name ?? '')),
            ])
            ->filter(static fn (array $row): bool => $row['term_id'] > 0 && $row['term_name'] !== '')
            ->values()
            ->all();
    }
}
