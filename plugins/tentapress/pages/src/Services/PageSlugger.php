<?php

declare(strict_types=1);

namespace TentaPress\Pages\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class PageSlugger
{
    private array $reserved = [
        'admin',
        'api',
        'login',
        'logout',
        'storage',
        'vendor',
        'assets',
        'build',
        'favicon.ico',
        'robots.txt',
        'sitemap.xml',
    ];

    public function normalize(string $titleOrSlug): string
    {
        $slug = Str::slug($titleOrSlug);
        $slug = trim($slug);

        throw_if($slug === '', RuntimeException::class, 'Unable to generate a slug.');

        throw_if(in_array($slug, $this->reserved, true), RuntimeException::class, "The slug '{$slug}' is reserved.");

        return $slug;
    }

    public function unique(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $this->normalize($baseSlug);

        $exists = $this->exists($slug, $ignoreId);
        if (! $exists) {
            return $slug;
        }

        $i = 2;
        while (true) {
            $candidate = "{$slug}-{$i}";
            if (! $this->exists($candidate, $ignoreId)) {
                return $candidate;
            }
            $i++;
        }
    }

    private function exists(string $slug, ?int $ignoreId): bool
    {
        $q = DB::table('tp_pages')->where('slug', $slug);

        if ($ignoreId !== null) {
            $q->where('id', '!=', $ignoreId);
        }

        return $q->exists();
    }
}
