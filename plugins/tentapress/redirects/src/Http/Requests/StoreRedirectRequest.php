<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_path' => ['required', 'string', 'max:1024', 'regex:/^\/?[A-Za-z0-9\-\/._~%]+$/', Rule::unique('tp_redirects', 'source_path')],
            'target_path' => ['required', 'string', 'max:1024', 'regex:/^\/?[A-Za-z0-9\-\/._~%]+$/'],
            'status_code' => ['required', Rule::in([301, 302])],
            'is_enabled' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'source_path.regex' => 'Source path must be a valid relative URL path.',
            'target_path.regex' => 'Target path must be a valid relative URL path.',
        ];
    }
}
