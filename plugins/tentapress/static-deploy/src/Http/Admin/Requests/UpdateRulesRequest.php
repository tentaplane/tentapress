<?php

declare(strict_types=1);

namespace TentaPress\StaticDeploy\Http\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use TentaPress\StaticDeploy\Support\StaticReplacementRules;

final class UpdateRulesRequest extends FormRequest
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
            'rules_action' => ['nullable', 'string', 'in:save,load_example,reset'],
            'replacement_rules_json' => [
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($this->input('rules_action', 'save') !== 'save') {
                        return;
                    }

                    try {
                        StaticReplacementRules::normalize((string) $value);
                    } catch (\InvalidArgumentException $exception) {
                        $fail($exception->getMessage());
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string,string>
     */
    public function messages(): array
    {
        return [
            'rules_action.in' => 'Unknown replacement rules action.',
            'replacement_rules_json.string' => 'Replacement rules must be a JSON string.',
        ];
    }
}
