<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Http\Admin\Terms;

use Illuminate\Contracts\View\View;
use TentaPress\Taxonomies\Models\TpTaxonomy;
use TentaPress\Taxonomies\Models\TpTerm;

final class EditController
{
    public function __invoke(TpTaxonomy $taxonomy, TpTerm $term): View
    {
        abort_unless((int) $term->taxonomy_id === (int) $taxonomy->id, 404);

        return view('tentapress-taxonomies::terms.form', [
            'mode' => 'edit',
            'taxonomy' => $taxonomy,
            'term' => $term,
            'parentOptions' => $taxonomy->terms()
                ->whereKeyNot($term->id)
                ->orderBy('name')
                ->get(),
        ]);
    }
}
