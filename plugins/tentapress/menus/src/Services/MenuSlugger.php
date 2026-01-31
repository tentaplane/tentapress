<?php

declare(strict_types=1);

namespace TentaPress\Menus\Services;

use Illuminate\Support\Str;
use TentaPress\Menus\Models\TpMenu;

final class MenuSlugger
{
    public function unique(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug(trim($value));
        $base = $base !== '' ? $base : 'menu';

        $slug = $base;
        $suffix = 2;

        while ($this->exists($slug, $ignoreId)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function exists(string $slug, ?int $ignoreId): bool
    {
        $query = TpMenu::query()->where('slug', $slug);

        if ($ignoreId !== null && $ignoreId > 0) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }
}
