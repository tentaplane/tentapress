<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Http\Admin;

use Illuminate\Auth\Access\AuthorizationException;
use InvalidArgumentException;
use TentaPress\Workflow\Http\Requests\UpdateWorkflowAssignmentsRequest;
use TentaPress\Workflow\Services\WorkflowManager;

final class UpdateAssignmentsController
{
    use WorkflowControllerSupport;

    public function __invoke(UpdateWorkflowAssignmentsRequest $request, string $resourceType, int $resourceId, WorkflowManager $manager)
    {
        try {
            $manager->assign($resourceType, $resourceId, $this->actor(), [
                'owner_user_id' => $this->normalizeUserId($request->validated('owner_user_id')),
                'reviewer_user_id' => $this->normalizeUserId($request->validated('reviewer_user_id')),
                'approver_user_id' => $this->normalizeUserId($request->validated('approver_user_id')),
            ]);
        } catch (AuthorizationException $exception) {
            throw $exception;
        } catch (InvalidArgumentException $exception) {
            return $this->redirectToResource($resourceType, $resourceId, $exception->getMessage(), 'error');
        }

        return $this->redirectToResource($resourceType, $resourceId, 'Workflow assignments updated.');
    }

    private function normalizeUserId(mixed $value): ?int
    {
        $userId = is_numeric($value) ? (int) $value : 0;

        return $userId > 0 ? $userId : null;
    }
}
