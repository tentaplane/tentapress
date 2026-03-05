<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Http\Admin;

use Illuminate\Contracts\View\View;
use TentaPress\Taxonomies\Models\TpTaxonomy;

final class IndexController
{
    public function __invoke(): View
    {
        $taxonomies = TpTaxonomy::query()
            ->withCount('terms')
            ->orderBy('label')
            ->get();

        return view('tentapress-taxonomies::taxonomies.index', [
            'taxonomies' => $taxonomies,
        ]);
    }
}
