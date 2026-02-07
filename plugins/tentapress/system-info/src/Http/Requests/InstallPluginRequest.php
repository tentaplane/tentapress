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
            'package' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string,string>
     */
    public function messages(): array
    {
        return [
            'package.required' => 'A package name is required.',
        ];
    }
}
