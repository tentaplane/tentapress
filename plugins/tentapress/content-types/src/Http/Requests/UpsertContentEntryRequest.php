<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use TentaPress\ContentTypes\Models\TpContentEntry;
use TentaPress\ContentTypes\Models\TpContentType;
use TentaPress\ContentTypes\Services\ContentEntryEditorDriverResolver;

final class UpsertContentEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $contentType = $this->route('contentType');
        $entry = $this->route('entry');
        $contentTypeId = $contentType instanceof TpContentType ? (int) $contentType->id : 0;
        $entryId = $entry instanceof TpContentEntry ? (int) $entry->id : null;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('tp_content_entries', 'slug')
                    ->where(fn ($query) => $query->where('content_type_id', $contentTypeId))
                    ->ignore($entryId),
            ],
            'layout' => ['nullable', 'string', 'max:120'],
            'editor_driver' => ['nullable', Rule::in(resolve(ContentEntryEditorDriverResolver::class)->allowedIds())],
            'blocks_json' => ['nullable', 'string'],
            'page_doc_json' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
            'field_values' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug must use lowercase kebab-case.',
            'editor_driver.in' => 'Choose a valid editor driver.',
            'published_at.date' => 'Enter a valid publication date and time.',
        ];
    }
}
