<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TentaPress\Revisions\Models\TpRevision;
use TentaPress\Users\Models\TpUser;

final class TpWorkflowItem extends Model
{
    protected $table = 'tp_workflow_items';

    protected $fillable = [
        'resource_type',
        'resource_id',
        'editorial_state',
        'owner_user_id',
        'reviewer_user_id',
        'approver_user_id',
        'pending_revision_id',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'scheduled_publish_at',
        'last_transitioned_by',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'scheduled_publish_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(TpUser::class, 'owner_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(TpUser::class, 'reviewer_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(TpUser::class, 'approver_user_id');
    }

    public function pendingRevision(): BelongsTo
    {
        return $this->belongsTo(TpRevision::class, 'pending_revision_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(TpUser::class, 'last_transitioned_by');
    }

    public function hasPendingRevision(): bool
    {
        return (int) ($this->pending_revision_id ?? 0) > 0;
    }

    public function nextActionLabel(): string
    {
        return match ((string) $this->editorial_state) {
            WorkflowState::InReview => $this->reviewer_user_id !== null ? 'Awaiting reviewer' : 'Awaiting approval',
            WorkflowState::ChangesRequested => 'Changes requested',
            WorkflowState::Approved => $this->scheduled_publish_at !== null ? 'Scheduled for publish' : 'Ready to publish',
            default => 'Draft in progress',
        };
    }
}
