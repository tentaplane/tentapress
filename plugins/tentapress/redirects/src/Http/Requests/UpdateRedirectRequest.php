<?php

declare(strict_types=1);

namespace TentaPress\Redirects\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use TentaPress\Redirects\Models\TpRedirect;

final class UpdateRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var TpRedirect|null $redirect */
        $redirect = $this->route('redirect');

        return [
            'source_path' => ['required', 'string', 'max:1024', 'regex:/^\/?[A-Za-z0-9\-\/._~%]+$/', Rule::unique('tp_redirects', 'source_path')->ignore($redirect?->id)],
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
