<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use TentaPress\ContentTypes\Http\Requests\UpsertContentTypeRequest;
use TentaPress\ContentTypes\Models\TpContentType;
use TentaPress\ContentTypes\Services\ContentTypeBasePathValidator;
use TentaPress\ContentTypes\Services\ContentFieldSchemaNormalizer;

final class StoreController
{
    public function __invoke(
        UpsertContentTypeRequest $request,
        ContentTypeBasePathValidator $basePathValidator,
        ContentFieldSchemaNormalizer $fieldSchemaNormalizer,
    ): RedirectResponse {
        $data = $request->validated();

        try {
            $basePath = $basePathValidator->assertAvailable((string) $data['base_path']);
            $fields = $fieldSchemaNormalizer->normalize($this->decodeFieldsJson((string) ($data['fields_json'] ?? '[]')));
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'fields_json' => $exception->getMessage(),
            ]);
        }

        $contentType = TpContentType::query()->create([
            'key' => (string) $data['key'],
            'singular_label' => (string) $data['singular_label'],
            'plural_label' => (string) $data['plural_label'],
            'description' => $data['description'] ?? null,
            'base_path' => $basePath,
            'default_layout' => $data['default_layout'] ?? null,
            'default_editor_driver' => (string) ($data['default_editor_driver'] ?? 'blocks'),
            'archive_enabled' => $request->boolean('archive_enabled', true),
            'api_visibility' => (string) $data['api_visibility'],
        ]);

        foreach ($fields as $field) {
            $contentType->fields()->create($field);
        }

        return to_route('tp.content-types.edit', ['contentType' => $contentType->id])
            ->with('tp_notice_success', 'Content type created.');
    }

    /**
     * @return array<int,mixed>
     */
    private function decodeFieldsJson(string $raw): array
    {
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
