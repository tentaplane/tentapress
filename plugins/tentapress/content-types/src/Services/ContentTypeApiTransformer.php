<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Services;

use TentaPress\ContentTypes\Models\TpContentEntry;
use TentaPress\ContentTypes\Models\TpContentType;

final class ContentTypeApiTransformer
{
    /**
     * @return array<string,mixed>
     */
    public function transformType(TpContentType $contentType): array
    {
        return [
            'key' => (string) $contentType->key,
            'singular_label' => (string) $contentType->singular_label,
            'plural_label' => (string) $contentType->plural_label,
            'description' => (string) ($contentType->description ?? ''),
            'base_path' => (string) $contentType->base_path,
            'archive_enabled' => (bool) $contentType->archive_enabled,
            'api_visibility' => (string) $contentType->api_visibility,
            'default_layout' => (string) ($contentType->default_layout ?? ''),
            'default_editor_driver' => (string) ($contentType->default_editor_driver ?? 'blocks'),
            'fields' => $contentType->fields->map(fn ($field): array => [
                'key' => (string) $field->key,
                'label' => (string) $field->label,
                'field_type' => (string) $field->field_type,
                'required' => (bool) $field->required,
                'config' => is_array($field->config) ? $field->config : [],
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function transformEntry(TpContentEntry $entry): array
    {
        $entry->loadMissing('contentType.fields');

        return [
            'id' => (int) $entry->id,
            'type' => (string) ($entry->contentType?->key ?? ''),
            'title' => (string) $entry->title,
            'slug' => (string) $entry->slug,
            'status' => (string) $entry->status,
            'layout' => (string) ($entry->layout ?? ''),
            'published_at' => $entry->published_at?->toAtomString(),
            'permalink' => $entry->permalink(),
            'fields' => is_array($entry->field_values) ? $entry->field_values : [],
            'content_raw' => [
                'editor_driver' => (string) ($entry->editor_driver ?? 'blocks'),
                'blocks' => is_array($entry->blocks) ? $entry->blocks : [],
                'content' => is_array($entry->content) ? $entry->content : null,
            ],
        ];
    }
}
