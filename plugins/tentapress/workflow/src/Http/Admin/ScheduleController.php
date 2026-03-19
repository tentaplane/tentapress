<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Http\Admin;

use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use InvalidArgumentException;
use TentaPress\Workflow\Http\Requests\ScheduleWorkflowRequest;
use TentaPress\Workflow\Services\WorkflowManager;

final class ScheduleController
{
    use WorkflowControllerSupport;

    public function __invoke(ScheduleWorkflowRequest $request, string $resourceType, int $resourceId, WorkflowManager $manager)
    {
        try {
            $manager->schedule($resourceType, $resourceId, $this->actor(), Carbon::parse((string) $request->validated('scheduled_publish_at')));
        } catch (AuthorizationException $exception) {
            throw $exception;
        } catch (InvalidArgumentException $exception) {
            return $this->redirectToResource($resourceType, $resourceId, $exception->getMessage(), 'error');
        }

        return $this->redirectToResource($resourceType, $resourceId, 'Publish scheduled.');
    }
}
