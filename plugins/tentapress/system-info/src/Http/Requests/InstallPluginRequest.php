<?php

declare(strict_types=1);

namespace TentaPress\SystemInfo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InstallPluginRequest extends FormRequest
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
            'package' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9][a-z0-9_.-]*\/[a-z0-9][a-z0-9_.-]*$/'],
        ];
    }

    /**
     * @return array<string,string>
     */
    public function messages(): array
    {
        return [
            'package.required' => 'A package name is required.',
            'package.regex' => 'Package must match vendor/package.',
        ];
    }
}
