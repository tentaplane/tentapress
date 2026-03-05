<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Http\Admin\Terms;

use Illuminate\Http\RedirectResponse;
use TentaPress\Taxonomies\Http\Requests\UpdateTermRequest;
use TentaPress\Taxonomies\Models\TpTaxonomy;
use TentaPress\Taxonomies\Models\TpTerm;
use TentaPress\Taxonomies\Support\TermSlugger;

final readonly class UpdateController
{
    public function __construct(
        private TermSlugger $slugger,
    ) {
    }

    public function __invoke(UpdateTermRequest $request, TpTaxonomy $taxonomy, TpTerm $term): RedirectResponse
    {
        abort_unless((int) $term->taxonomy_id === (int) $taxonomy->id, 404);

        $data = $request->validated();

        $term->fill([
            'parent_id' => $this->resolveParentId($taxonomy, $term, $data),
            'name' => (string) $data['name'],
            'slug' => $this->slugger->unique(
                $taxonomy,
                (string) ($data['slug'] ?? ''),
                (string) $data['name'],
                (int) $term->id,
            ),
            'description' => $this->nullableString($data['description'] ?? null),
        ]);

        $term->save();

        return to_route('tp.taxonomies.terms.edit', ['taxonomy' => $taxonomy->id, 'term' => $term->id])
            ->with('tp_notice_success', 'Term updated.');
    }

    /**
     * @param  array<string,mixed>  $data
     */
    private function resolveParentId(TpTaxonomy $taxonomy, TpTerm $term, array $data): ?int
    {
        if (! $taxonomy->is_hierarchical) {
            return null;
        }

        $parentId = (int) ($data['parent_id'] ?? 0);

        if ($parentId <= 0 || $parentId === (int) $term->id) {
            return null;
        }

        return $parentId;
    }

    private function nullableString(mixed $value): ?string
    {
        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
