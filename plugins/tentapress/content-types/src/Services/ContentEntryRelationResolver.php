<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Services;

use Illuminate\Database\Eloquent\Collection;
use TentaPress\ContentTypes\Models\TpContentEntry;

final class ContentEntryRelationResolver
{
    public function find(int $entryId): ?TpContentEntry
    {
        return TpContentEntry::query()
            ->with('contentType')
            ->find($entryId);
    }

    /**
     * @param  array<int,string>  $allowedTypeKeys
     * @return Collection<int,TpContentEntry>
     */
    public function options(array $allowedTypeKeys = []): Collection
    {
        return TpContentEntry::query()
            ->with('contentType')
            ->when(
                $allowedTypeKeys !== [],
                fn ($query) => $query->whereHas('contentType', fn ($nested) => $nested->whereIn('key', $allowedTypeKeys))
            )
            ->orderBy('title')
            ->get();
    }
}
