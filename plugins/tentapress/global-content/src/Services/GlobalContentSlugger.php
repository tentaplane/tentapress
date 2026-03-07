<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Services;

use Illuminate\Support\Str;
use TentaPress\GlobalContent\Models\TpGlobalContent;

final class GlobalContentSlugger
{
    public function unique(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);
        $base = $base !== '' ? $base : 'global-content';
        $slug = $base;
        $suffix = 2;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId): bool
    {
        return TpGlobalContent::query()
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists();
    }
}
