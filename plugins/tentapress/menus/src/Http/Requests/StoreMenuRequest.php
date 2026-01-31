<?php

declare(strict_types=1);

namespace TentaPress\Menus\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreMenuRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('tp_menus', 'slug'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Menu name is required.',
            'name.max' => 'Menu name may not be longer than 255 characters.',
            'slug.regex' => 'Slug may only contain lowercase letters, numbers, and dashes.',
            'slug.unique' => 'That slug is already in use by another menu.',
        ];
    }
}
