<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Http\Admin\Terms;

use Illuminate\Contracts\View\View;
use TentaPress\Taxonomies\Models\TpTaxonomy;

final class TermsIndexController
{
    public function __invoke(TpTaxonomy $taxonomy): View
    {
        $terms = $taxonomy->terms()
            ->with(['parent', 'children'])
            ->withCount('assignments')
            ->orderBy('name')
            ->get();

        return view('tentapress-taxonomies::terms.index', [
            'taxonomy' => $taxonomy,
            'terms' => $terms,
        ]);
    }
}
