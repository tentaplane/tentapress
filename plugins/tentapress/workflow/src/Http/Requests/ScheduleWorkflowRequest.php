<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ScheduleWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scheduled_publish_at' => ['required', 'date', 'after:now'],
        ];
    }
}
