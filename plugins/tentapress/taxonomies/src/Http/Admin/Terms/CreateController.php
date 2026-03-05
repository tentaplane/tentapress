<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Http\Admin\Terms;

use Illuminate\Contracts\View\View;
use TentaPress\Taxonomies\Models\TpTaxonomy;

final class CreateController
{
    public function __invoke(TpTaxonomy $taxonomy): View
    {
        return view('tentapress-taxonomies::terms.form', [
            'mode' => 'create',
            'taxonomy' => $taxonomy,
            'term' => null,
            'parentOptions' => $taxonomy->terms()->orderBy('name')->get(),
        ]);
    }
}
