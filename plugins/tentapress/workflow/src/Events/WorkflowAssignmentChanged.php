<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Events;

use TentaPress\Workflow\Models\TpWorkflowItem;

final readonly class WorkflowAssignmentChanged
{
    /**
     * @param array<string,array{from:?int,to:?int}> $changes
     */
    public function __construct(
        public TpWorkflowItem $item,
        public array $changes,
    ) {
    }
}
