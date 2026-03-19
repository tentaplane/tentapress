<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Events;

use TentaPress\Workflow\Models\TpWorkflowItem;

final readonly class WorkflowApprovalChanged
{
    public function __construct(
        public TpWorkflowItem $item,
        public string $action,
    ) {
    }
}
