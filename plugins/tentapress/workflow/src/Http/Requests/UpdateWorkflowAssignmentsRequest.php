<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateWorkflowAssignmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'owner_user_id' => ['nullable', 'integer', Rule::exists('tp_users', 'id')],
            'reviewer_user_id' => ['nullable', 'integer', Rule::exists('tp_users', 'id')],
            'approver_user_id' => ['nullable', 'integer', Rule::exists('tp_users', 'id')],
        ];
    }
}
