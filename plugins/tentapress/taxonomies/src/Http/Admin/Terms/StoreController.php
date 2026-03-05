<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Http\Admin\Terms;

use Illuminate\Http\RedirectResponse;
use TentaPress\Taxonomies\Http\Requests\StoreTermRequest;
use TentaPress\Taxonomies\Models\TpTaxonomy;
use TentaPress\Taxonomies\Models\TpTerm;
use TentaPress\Taxonomies\Support\TermSlugger;

final readonly class StoreController
{
    public function __construct(
        private TermSlugger $slugger,
    ) {
    }

    public function __invoke(StoreTermRequest $request, TpTaxonomy $taxonomy): RedirectResponse
    {
        $data = $request->validated();

        TpTerm::query()->create([
            'taxonomy_id' => $taxonomy->id,
            'parent_id' => $this->resolveParentId($taxonomy, $data),
            'name' => (string) $data['name'],
            'slug' => $this->slugger->unique(
                $taxonomy,
                (string) ($data['slug'] ?? ''),
                (string) $data['name'],
            ),
            'description' => $this->nullableString($data['description'] ?? null),
        ]);

        return to_route('tp.taxonomies.terms.index', ['taxonomy' => $taxonomy->id])
            ->with('tp_notice_success', 'Term created.');
    }

    /**
     * @param  array<string,mixed>  $data
     */
    private function resolveParentId(TpTaxonomy $taxonomy, array $data): ?int
    {
        if (! $taxonomy->is_hierarchical) {
            return null;
        }

        $parentId = (int) ($data['parent_id'] ?? 0);

        return $parentId > 0 ? $parentId : null;
    }

    private function nullableString(mixed $value): ?string
    {
        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
