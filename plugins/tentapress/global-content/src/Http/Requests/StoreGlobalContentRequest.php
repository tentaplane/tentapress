<?php

declare(strict_types=1);

namespace TentaPress\GlobalContent\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use TentaPress\GlobalContent\Services\GlobalContentFormData;

final class StoreGlobalContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string,mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('tp_global_contents', 'slug')],
            'kind' => ['required', Rule::in(['section', 'template_part'])],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'description' => ['nullable', 'string'],
            'editor_driver' => ['required', Rule::in(resolve(GlobalContentFormData::class)->editorDriverIds())],
            'blocks_json' => ['nullable', 'string'],
        ];
    }
}
