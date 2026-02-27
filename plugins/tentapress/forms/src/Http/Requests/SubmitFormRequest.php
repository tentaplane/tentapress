<?php

declare(strict_types=1);

namespace TentaPress\Forms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SubmitFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string,array<int,mixed>>
     */
    public function rules(): array
    {
        return [
            '_tp_payload' => ['required', 'string', 'max:20000'],
            '_tp_hp' => ['nullable', 'string', 'max:255'],
            '_tp_started_at' => ['required', 'integer', 'min:1'],
            '_tp_return_url' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
