<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Admin\Entries;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use TentaPress\ContentTypes\Http\Requests\UpsertContentEntryRequest;
use TentaPress\ContentTypes\Models\TpContentEntry;
use TentaPress\ContentTypes\Models\TpContentType;
use TentaPress\ContentTypes\Services\ContentEntryEditorDriverResolver;
use TentaPress\ContentTypes\Services\ContentEntryFieldValueNormalizer;
use TentaPress\ContentTypes\Services\ContentEntrySlugger;
use TentaPress\ContentTypes\Support\BlocksNormalizer;

final readonly class StoreController
{
    public function __construct(
        private BlocksNormalizer $normalizer,
    ) {
    }

    public function __invoke(
        UpsertContentEntryRequest $request,
        TpContentType $contentType,
        ContentEntrySlugger $slugger,
        ContentEntryFieldValueNormalizer $fieldValueNormalizer,
        ContentEntryEditorDriverResolver $editorDrivers,
    ): RedirectResponse {
        $contentType->load('fields');
        $data = $request->validated();

        try {
            $fieldValues = $fieldValueNormalizer->normalize($contentType, $request->input('field_values', []));
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'field_values' => $exception->getMessage(),
            ]);
        }

        $blocks = $this->normalizer->normalize(json_decode((string) ($data['blocks_json'] ?? '[]'), true));
        $content = json_decode((string) ($data['page_doc_json'] ?? 'null'), true);
        $content = is_array($content) ? $content : null;
        $actorId = Auth::check() && is_object(Auth::user()) ? (int) (Auth::user()->id ?? 0) : null;

        $entry = TpContentEntry::query()->create([
            'content_type_id' => (int) $contentType->id,
            'title' => (string) $data['title'],
            'slug' => $slugger->unique((int) $contentType->id, (string) $data['slug']),
            'status' => 'draft',
            'layout' => $data['layout'] ?? $contentType->default_layout,
            'editor_driver' => $editorDrivers->resolve((string) ($data['editor_driver'] ?? $contentType->default_editor_driver)),
            'blocks' => $blocks,
            'content' => $content,
            'field_values' => $fieldValues,
            'created_by' => $actorId ?: null,
            'updated_by' => $actorId ?: null,
        ]);

        return to_route('tp.content-types.entries.edit', ['contentType' => $contentType->id, 'entry' => $entry->id])
            ->with('tp_notice_success', 'Content entry created.');
    }
}
