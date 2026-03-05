<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Http\Admin\Terms;

use Illuminate\Http\RedirectResponse;
use TentaPress\Taxonomies\Models\TpTaxonomy;
use TentaPress\Taxonomies\Models\TpTerm;

final class DestroyController
{
    public function __invoke(TpTaxonomy $taxonomy, TpTerm $term): RedirectResponse
    {
        abort_unless((int) $term->taxonomy_id === (int) $taxonomy->id, 404);

        if ($term->children()->exists()) {
            return to_route('tp.taxonomies.terms.index', ['taxonomy' => $taxonomy->id])
                ->with('tp_notice_error', 'Delete child terms before removing this term.');
        }

        if ($term->assignments()->exists()) {
            return to_route('tp.taxonomies.terms.index', ['taxonomy' => $taxonomy->id])
                ->with('tp_notice_error', 'Remove term assignments before deleting this term.');
        }

        $term->delete();

        return to_route('tp.taxonomies.terms.index', ['taxonomy' => $taxonomy->id])
            ->with('tp_notice_success', 'Term deleted.');
    }
}
