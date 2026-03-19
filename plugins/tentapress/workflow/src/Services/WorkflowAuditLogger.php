<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Services;

use TentaPress\Workflow\Models\TpWorkflowEvent;
use TentaPress\Workflow\Models\TpWorkflowItem;

final class WorkflowAuditLogger
{
    /**
     * @param array<string,mixed> $metadata
     */
    public function log(
        TpWorkflowItem $item,
        string $eventType,
        ?string $fromState,
        ?string $toState,
        ?int $actorUserId,
        array $metadata = [],
    ): TpWorkflowEvent {
        return TpWorkflowEvent::query()->create([
            'workflow_item_id' => (int) $item->id,
            'resource_type' => (string) $item->resource_type,
            'resource_id' => (int) $item->resource_id,
            'event_type' => $eventType,
            'from_state' => $fromState,
            'to_state' => $toState,
            'actor_user_id' => $actorUserId,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
