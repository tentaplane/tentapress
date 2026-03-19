<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Http\Admin;

use Illuminate\Auth\Access\AuthorizationException;
use InvalidArgumentException;
use TentaPress\Workflow\Services\WorkflowManager;

final class RequestChangesController
{
    use WorkflowControllerSupport;

    public function __invoke(string $resourceType, int $resourceId, WorkflowManager $manager)
    {
        try {
            $manager->requestChanges($resourceType, $resourceId, $this->actor());
        } catch (AuthorizationException $exception) {
            throw $exception;
        } catch (InvalidArgumentException $exception) {
            return $this->redirectToResource($resourceType, $resourceId, $exception->getMessage(), 'error');
        }

        return $this->redirectToResource($resourceType, $resourceId, 'Changes requested.');
    }
}
