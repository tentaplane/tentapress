<?php

declare(strict_types=1);

namespace TentaPress\ContentTypes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use TentaPress\ContentTypes\Models\TpContentType;

final class UpsertContentTypeRequest extends FormRequest
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
        $contentTypeId = $contentType instanceof TpContentType ? (int) $contentType->id : null;

        return [
            'key' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('tp_content_types', 'key')->ignore($contentTypeId)],
            'singular_label' => ['required', 'string', 'max:120'],
            'plural_label' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'base_path' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('tp_content_types', 'base_path')->ignore($contentTypeId)],
            'default_layout' => ['nullable', 'string', 'max:120'],
            'default_editor_driver' => ['nullable', 'string', 'max:120'],
            'archive_enabled' => ['nullable', 'boolean'],
            'api_visibility' => ['required', Rule::in(['disabled', 'public'])],
            'fields_json' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'key.regex' => 'The content type key must use lowercase kebab-case.',
            'base_path.regex' => 'The base path must use lowercase kebab-case.',
            'api_visibility.in' => 'Choose a valid API visibility option.',
        ];
    }
}
