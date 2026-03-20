<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class ContentEntrySlugger
{
    public function normalize(string $titleOrSlug): string
    {
        $slug = Str::slug($titleOrSlug);
        $slug = trim($slug);

        throw_if($slug === '', RuntimeException::class, 'Unable to generate a slug.');

        return $slug;
    }

    public function unique(int $contentTypeId, string $baseSlug, ?int $ignoreEntryId = null): string
    {
        $slug = $this->normalize($baseSlug);

        if (! $this->exists($contentTypeId, $slug, $ignoreEntryId)) {
            return $slug;
        }

        $counter = 2;

        while (true) {
            $candidate = $slug.'-'.$counter;

            if (! $this->exists($contentTypeId, $candidate, $ignoreEntryId)) {
                return $candidate;
            }

            $counter++;
        }
    }

    private function exists(int $contentTypeId, string $slug, ?int $ignoreEntryId): bool
    {
        $query = DB::table('tp_content_entries')
            ->where('content_type_id', $contentTypeId)
            ->where('slug', $slug);

        if ($ignoreEntryId !== null) {
            $query->where('id', '!=', $ignoreEntryId);
        }

        return $query->exists();
    }
}
