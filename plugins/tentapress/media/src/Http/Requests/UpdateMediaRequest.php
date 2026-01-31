<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateMediaRequest extends FormRequest
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
            'title' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.max' => 'Title may not be longer than 255 characters.',
            'alt_text.max' => 'Alt text may not be longer than 255 characters.',
            'caption.max' => 'Caption may not be longer than 2000 characters.',
        ];
    }
}
