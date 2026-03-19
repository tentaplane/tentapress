<?php

declare(strict_types=1);

namespace TentaPress\PluginBoilerplate\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePluginBoilerplateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plugin_enabled' => ['nullable', 'boolean'],
            'endpoint_prefix' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'admin_notice' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'endpoint_prefix.regex' => 'Endpoint prefix must use lowercase kebab-case.',
        ];
    }
}
