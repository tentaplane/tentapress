<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PluginActionRequest extends FormRequest
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
        return [
            'id' => ['required', 'string', 'regex:/^[a-z0-9-]+\\/[a-z0-9-]+$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.required' => 'A plugin id is required.',
            'id.regex' => 'Plugin id must match vendor/name.',
        ];
    }
}
