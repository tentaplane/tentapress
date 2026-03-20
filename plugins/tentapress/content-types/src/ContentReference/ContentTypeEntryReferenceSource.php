<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\ContentReference;

use TentaPress\ContentTypes\Models\TpContentEntry;
use TentaPress\System\ContentReference\ContentReference;
use TentaPress\System\ContentReference\ContentReferenceSource;

final class ContentTypeEntryReferenceSource implements ContentReferenceSource
{
    public function key(): string
    {
        return 'content-types';
    }

    public function find(string $id): ?ContentReference
    {
        $entry = TpContentEntry::query()
            ->with('contentType')
            ->find((int) $id);

        if (! $entry instanceof TpContentEntry) {
            return null;
        }

        return new ContentReference(
            source: $this->key(),
            id: (string) $entry->getKey(),
            title: $this->titleFor($entry),
            typeLabel: (string) ($entry->contentType?->singular_label ?: 'Content Entry'),
            meta: [
                'content_type_key' => (string) ($entry->contentType?->key ?? ''),
            ],
        );
    }

    public function options(array $constraints = []): array
    {
        $allowedTypeKeys = collect($constraints['allowed_type_keys'] ?? [])
            ->map(fn (mixed $typeKey): string => trim((string) $typeKey))
            ->filter()
            ->values()
            ->all();

        return TpContentEntry::query()
            ->with('contentType')
            ->when(
                $allowedTypeKeys !== [],
                fn ($query) => $query->whereHas('contentType', fn ($nested) => $nested->whereIn('key', $allowedTypeKeys))
            )
            ->orderBy('title')
            ->get()
            ->map(fn (TpContentEntry $entry): ContentReference => new ContentReference(
                source: $this->key(),
                id: (string) $entry->getKey(),
                title: $this->titleFor($entry),
                typeLabel: (string) ($entry->contentType?->singular_label ?: 'Content Entry'),
                meta: [
                    'content_type_key' => (string) ($entry->contentType?->key ?? ''),
                ],
            ))
            ->all();
    }

    private function titleFor(TpContentEntry $entry): string
    {
        $title = trim((string) $entry->title);

        return $title !== '' ? $title : 'Content Entry #'.$entry->getKey();
    }
}
