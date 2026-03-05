<?php

declare(strict_types=1);

namespace TentaPress\Taxonomies\Support;

use Illuminate\Support\Str;
use TentaPress\Taxonomies\Models\TpTaxonomy;
use TentaPress\Taxonomies\Models\TpTerm;

final class TermSlugger
{
    public function unique(TpTaxonomy $taxonomy, string $requestedSlug, string $fallback, int $ignoreId = 0): string
    {
        $base = trim($requestedSlug) !== '' ? trim($requestedSlug) : Str::slug($fallback);
        $base = trim($base, '-');

        if ($base === '') {
            $base = 'term';
        }

        $slug = $base;
        $suffix = 2;

        while ($this->exists($taxonomy, $slug, $ignoreId)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function exists(TpTaxonomy $taxonomy, string $slug, int $ignoreId): bool
    {
        return TpTerm::query()
            ->where('taxonomy_id', $taxonomy->id)
            ->where('slug', $slug)
            ->when($ignoreId > 0, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }
}
