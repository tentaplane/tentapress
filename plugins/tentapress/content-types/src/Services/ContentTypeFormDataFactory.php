<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Services;

use TentaPress\ContentTypes\Models\TpContentType;
use TentaPress\ContentTypes\Models\TpContentTypeField;

final readonly class ContentTypeFormDataFactory
{
    public function __construct(
        private ContentEntryRelationResolver $relations,
    ) {
    }

    /**
     * @return array<string,array<int,array<string,mixed>>>
     */
    public function relationOptions(TpContentType $contentType): array
    {
        $options = [];

        foreach ($contentType->fields as $field) {
            if (! $field instanceof TpContentTypeField || (string) $field->field_type !== 'relation') {
                continue;
            }

            $allowedTypeKeys = collect($field->config['allowed_type_keys'] ?? [])
                ->map(fn (mixed $typeKey): string => trim((string) $typeKey))
                ->filter()
                ->values()
                ->all();

            $options[$field->key] = $this->relations->options($allowedTypeKeys)
                ->map(fn($entry): array => [
                    'id' => (int) $entry->id,
                    'title' => (string) $entry->title,
                    'type_label' => (string) ($entry->contentType?->singular_label ?? ''),
                ])
                ->all();
        }

        return $options;
    }
}
