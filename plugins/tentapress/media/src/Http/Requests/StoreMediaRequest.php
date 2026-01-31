<?php

declare(strict_types=1);

namespace TentaPress\Media\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreMediaRequest extends FormRequest
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
            'file' => ['required', 'file', 'max:51200'],
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
            'file.required' => 'Choose a file to upload.',
            'file.file' => 'The upload must be a valid file.',
            'file.max' => 'Files may not be larger than 50 MB.',
            'title.max' => 'Title may not be longer than 255 characters.',
            'alt_text.max' => 'Alt text may not be longer than 255 characters.',
            'caption.max' => 'Caption may not be longer than 2000 characters.',
        ];
    }
}
