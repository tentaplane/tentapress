<?php

declare(strict_types=1);

namespace TentaPress\Workflow\Services;

use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use TentaPress\Pages\Models\TpPage;
use TentaPress\Posts\Models\TpPost;
use TentaPress\Revisions\Models\TpRevision;
use TentaPress\Revisions\Services\RevisionRecorder;
use TentaPress\Users\Models\TpUser;
use TentaPress\Workflow\Events\WorkflowApprovalChanged;
use TentaPress\Workflow\Events\WorkflowAssignmentChanged;
use TentaPress\Workflow\Events\WorkflowPublishBlocked;
use TentaPress\Workflow\Events\WorkflowScheduledPublishExecuted;
use TentaPress\Workflow\Events\WorkflowStateChanged;
use TentaPress\Workflow\Models\TpWorkflowItem;
use TentaPress\Workflow\Models\WorkflowState;

final readonly class WorkflowManager
{
    public function __construct(
        private WorkflowResourceResolver $resources,
        private WorkflowAuditLogger $audit,
    ) {
    }

    public function ensureForResource(string $resourceType, int $resourceId, ?int $ownerUserId = null): TpWorkflowItem
    {
        $normalizedType = $this->resources->normalizeType($resourceType);

        return TpWorkflowItem::query()->firstOrCreate(
            [
                'resource_type' => $normalizedType,
                'resource_id' => $resourceId,
            ],
            [
                'editorial_state' => WorkflowState::Draft,
                'owner_user_id' => $ownerUserId,
            ],
        );
    }

    public function itemFor(string $resourceType, int $resourceId): ?TpWorkflowItem
    {
        return TpWorkflowItem::query()
            ->with(['owner', 'reviewer', 'approver', 'actor'])
            ->where('resource_type', $this->resources->normalizeType($resourceType))
            ->where('resource_id', $resourceId)
            ->first();
    }

    public function pendingRevisionFor(string $resourceType, int $resourceId): ?TpRevision
    {
        $item = $this->itemFor($resourceType, $resourceId);
        if (! $item instanceof TpWorkflowItem || (int) ($item->pending_revision_id ?? 0) <= 0) {
            return null;
        }

        return TpRevision::query()->find((int) $item->pending_revision_id);
    }

    public function queue(string $filter, ?TpUser $user, int $perPage = 20): LengthAwarePaginator
    {
        $query = TpWorkflowItem::query()
            ->with(['owner', 'reviewer', 'approver', 'actor'])
            ->orderByRaw('scheduled_publish_at is null')
            ->oldest('scheduled_publish_at')
            ->latest('updated_at');

        $filter = trim($filter);
        $userId = $user instanceof TpUser ? (int) $user->id : 0;

        if ($filter === 'mine' && $userId > 0) {
            $query->where(function ($builder) use ($userId): void {
                $builder->where('owner_user_id', $userId)
                    ->orWhere('reviewer_user_id', $userId)
                    ->orWhere('approver_user_id', $userId);
            });
        }

        if ($filter === 'review' && $userId > 0) {
            $query->where('editorial_state', WorkflowState::InReview)
                ->where(function ($builder) use ($userId): void {
                    $builder->where('reviewer_user_id', $userId)
                        ->orWhere('approver_user_id', $userId);
                });
        }

        if ($filter === 'scheduled') {
            $query->whereNotNull('scheduled_publish_at');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param array{owner_user_id:?int,reviewer_user_id:?int,approver_user_id:?int} $assignments
     */
    public function assign(string $resourceType, int $resourceId, TpUser $actor, array $assignments): TpWorkflowItem
    {
        $this->authorizeManageResource($actor, $resourceType);

        $item = $this->ensureForResource($resourceType, $resourceId, (int) $actor->id);
        $changes = [];

        foreach (['owner_user_id', 'reviewer_user_id', 'approver_user_id'] as $field) {
            $next = $assignments[$field] ?? null;
            $next = is_int($next) && $next > 0 ? $next : null;
            $current = $item->getAttribute($field);
            $current = is_int($current) && $current > 0 ? $current : null;

            if ($current === $next) {
                continue;
            }

            $changes[$field] = [
                'from' => $current,
                'to' => $next,
            ];

            $item->setAttribute($field, $next);
        }

        if ($changes === []) {
            return $item;
        }

        $item->last_transitioned_by = (int) $actor->id;
        $item->save();

        $this->audit->log($item, 'assignment_updated', null, null, (int) $actor->id, ['changes' => $changes]);
        event(new WorkflowAssignmentChanged($item->fresh(['owner', 'reviewer', 'approver', 'actor']), $changes));

        return $item->fresh(['owner', 'reviewer', 'approver', 'actor']);
    }

    public function submitForReview(string $resourceType, int $resourceId, TpUser $actor): TpWorkflowItem
    {
        $this->authorizeManageResource($actor, $resourceType);
        $item = $this->ensureForResource($resourceType, $resourceId, (int) $actor->id);
        $fromState = (string) $item->editorial_state;

        throw_unless(in_array($fromState, [WorkflowState::Draft, WorkflowState::ChangesRequested], true), InvalidArgumentException::class, 'Only draft workflow items can be submitted for review.');

        throw_if((int) ($item->reviewer_user_id ?? 0) <= 0 && (int) ($item->approver_user_id ?? 0) <= 0, InvalidArgumentException::class, 'Assign a reviewer or approver before submitting for review.');

        $item->editorial_state = WorkflowState::InReview;
        $item->submitted_at = now();
        $item->reviewed_at = null;
        $item->approved_at = null;
        $item->scheduled_publish_at = null;
        $item->last_transitioned_by = (int) $actor->id;
        $item->save();

        $this->recordStateChange($item, 'submitted_for_review', $fromState, WorkflowState::InReview, (int) $actor->id);

        return $item->fresh(['owner', 'reviewer', 'approver', 'actor']);
    }

    public function requestChanges(string $resourceType, int $resourceId, TpUser $actor): TpWorkflowItem
    {
        $item = $this->ensureForResource($resourceType, $resourceId, (int) $actor->id);
        $this->authorizeReviewer($actor, $item);
        $fromState = (string) $item->editorial_state;

        throw_if($fromState !== WorkflowState::InReview, InvalidArgumentException::class, 'Only items in review can request changes.');

        $item->editorial_state = WorkflowState::ChangesRequested;
        $item->reviewed_at = now();
        $item->approved_at = null;
        $item->scheduled_publish_at = null;
        $item->last_transitioned_by = (int) $actor->id;
        $item->save();

        $this->recordStateChange($item, 'changes_requested', $fromState, WorkflowState::ChangesRequested, (int) $actor->id);

        return $item->fresh(['owner', 'reviewer', 'approver', 'actor']);
    }

    public function approve(string $resourceType, int $resourceId, TpUser $actor): TpWorkflowItem
    {
        $item = $this->ensureForResource($resourceType, $resourceId, (int) $actor->id);
        $this->authorizeApprover($actor, $item);
        $fromState = (string) $item->editorial_state;

        throw_if($fromState !== WorkflowState::InReview, InvalidArgumentException::class, 'Only items in review can be approved.');

        $item->editorial_state = WorkflowState::Approved;
        $item->reviewed_at ??= now();
        $item->approved_at = now();
        $item->last_transitioned_by = (int) $actor->id;
        $item->save();

        $this->recordStateChange($item, 'approved', $fromState, WorkflowState::Approved, (int) $actor->id);
        event(new WorkflowApprovalChanged($item->fresh(['owner', 'reviewer', 'approver', 'actor']), 'approved'));

        return $item->fresh(['owner', 'reviewer', 'approver', 'actor']);
    }

    public function revokeApproval(string $resourceType, int $resourceId, TpUser $actor): TpWorkflowItem
    {
        $item = $this->ensureForResource($resourceType, $resourceId, (int) $actor->id);
        $this->authorizeApprover($actor, $item);
        $fromState = (string) $item->editorial_state;

        throw_if($fromState !== WorkflowState::Approved, InvalidArgumentException::class, 'Only approved items can revoke approval.');

        $item->editorial_state = WorkflowState::Draft;
        $item->approved_at = null;
        $item->scheduled_publish_at = null;
        $item->last_transitioned_by = (int) $actor->id;
        $item->save();

        $this->recordStateChange($item, 'approval_revoked', $fromState, WorkflowState::Draft, (int) $actor->id);
        event(new WorkflowApprovalChanged($item->fresh(['owner', 'reviewer', 'approver', 'actor']), 'revoked'));

        return $item->fresh(['owner', 'reviewer', 'approver', 'actor']);
    }

    public function schedule(string $resourceType, int $resourceId, TpUser $actor, CarbonInterface $publishAt): TpWorkflowItem
    {
        $item = $this->ensureForResource($resourceType, $resourceId, (int) $actor->id);
        $this->authorizePublisher($actor, $item);

        throw_if((string) $item->editorial_state !== WorkflowState::Approved, InvalidArgumentException::class, 'Only approved items can be scheduled.');

        throw_if($publishAt->lte(now()), InvalidArgumentException::class, 'Schedule time must be in the future.');

        $item->scheduled_publish_at = $publishAt;
        $item->last_transitioned_by = (int) $actor->id;
        $item->save();

        $this->audit->log($item, 'scheduled', null, null, (int) $actor->id, [
            'scheduled_publish_at' => $publishAt->toAtomString(),
        ]);

        return $item->fresh(['owner', 'reviewer', 'approver', 'actor']);
    }

    public function publishNow(string $resourceType, int $resourceId, TpUser $actor): TpWorkflowItem
    {
        $item = $this->ensureForResource($resourceType, $resourceId, (int) $actor->id);
        $this->authorizePublisher($actor, $item);

        if ((string) $item->editorial_state !== WorkflowState::Approved) {
            $this->audit->log($item, 'publish_blocked', (string) $item->editorial_state, (string) $item->editorial_state, (int) $actor->id, [
                'reason' => 'not_approved',
            ]);
            event(new WorkflowPublishBlocked($item->fresh(['owner', 'reviewer', 'approver', 'actor']), 'This item must be approved before publishing.'));

            throw new InvalidArgumentException('This item must be approved before publishing.');
        }

        return $this->publishItem($item, now(), (int) $actor->id, false);
    }

    public function unpublish(string $resourceType, int $resourceId, TpUser $actor): TpWorkflowItem
    {
        $item = $this->ensureForResource($resourceType, $resourceId, (int) $actor->id);
        $this->authorizePublisher($actor, $item);

        $resource = $this->resolveResourceOrFail($resourceType, $resourceId);
        $fromState = (string) $item->editorial_state;

        $resource->forceFill([
            'status' => 'draft',
            'updated_by' => (int) $actor->id,
        ])->save();

        $item->editorial_state = WorkflowState::Draft;
        $item->scheduled_publish_at = null;
        $item->approved_at = null;
        $item->last_transitioned_by = (int) $actor->id;
        $item->save();

        $this->recordStateChange($item, 'unpublished', $fromState, WorkflowState::Draft, (int) $actor->id);

        return $item->fresh(['owner', 'reviewer', 'approver', 'actor']);
    }

    public function publishDue(int $limit = 50): int
    {
        $items = TpWorkflowItem::query()
            ->where('editorial_state', WorkflowState::Approved)
            ->whereNotNull('scheduled_publish_at')
            ->where('scheduled_publish_at', '<=', now())
            ->oldest('scheduled_publish_at')
            ->limit($limit)
            ->get();

        $published = 0;

        foreach ($items as $item) {
            $this->publishItem($item, $item->scheduled_publish_at ?? now(), null, true);
            $published++;
        }

        return $published;
    }

    public function stagePublishedPageUpdate(TpPage $page, mixed $request, TpUser $actor): TpWorkflowItem
    {
        $this->authorizeManageResource($actor, 'pages');
        $item = $this->ensureForResource('pages', (int) $page->id, (int) $actor->id);
        $recorder = $this->resolveRecorder();
        $revision = $recorder->capturePageFromRequest($page, $request, 'workflow_pending');

        throw_unless($revision instanceof TpRevision, InvalidArgumentException::class, 'Unable to capture the workflow draft for this page.');

        $fromState = (string) $item->editorial_state;
        $item->pending_revision_id = (int) $revision->id;
        $item->editorial_state = WorkflowState::Draft;
        $item->submitted_at = null;
        $item->reviewed_at = null;
        $item->approved_at = null;
        $item->scheduled_publish_at = null;
        $item->last_transitioned_by = (int) $actor->id;
        $item->save();

        $this->audit->log($item, 'working_copy_staged', $fromState, WorkflowState::Draft, (int) $actor->id, [
            'pending_revision_id' => (int) $revision->id,
        ]);

        if ($fromState !== WorkflowState::Draft) {
            event(new WorkflowStateChanged($item->fresh(['owner', 'reviewer', 'approver', 'actor']), 'working_copy_staged', $fromState, WorkflowState::Draft));
        }

        return $item->fresh(['owner', 'reviewer', 'approver', 'actor']);
    }

    public function stagePublishedPostUpdate(TpPost $post, mixed $request, TpUser $actor): TpWorkflowItem
    {
        $this->authorizeManageResource($actor, 'posts');
        $item = $this->ensureForResource('posts', (int) $post->id, (int) $actor->id);
        $recorder = $this->resolveRecorder();
        $revision = $recorder->capturePostFromRequest($post, $request, 'workflow_pending');

        throw_unless($revision instanceof TpRevision, InvalidArgumentException::class, 'Unable to capture the workflow draft for this post.');

        $fromState = (string) $item->editorial_state;
        $item->pending_revision_id = (int) $revision->id;
        $item->editorial_state = WorkflowState::Draft;
        $item->submitted_at = null;
        $item->reviewed_at = null;
        $item->approved_at = null;
        $item->scheduled_publish_at = null;
        $item->last_transitioned_by = (int) $actor->id;
        $item->save();

        $this->audit->log($item, 'working_copy_staged', $fromState, WorkflowState::Draft, (int) $actor->id, [
            'pending_revision_id' => (int) $revision->id,
        ]);

        if ($fromState !== WorkflowState::Draft) {
            event(new WorkflowStateChanged($item->fresh(['owner', 'reviewer', 'approver', 'actor']), 'working_copy_staged', $fromState, WorkflowState::Draft));
        }

        return $item->fresh(['owner', 'reviewer', 'approver', 'actor']);
    }

    private function publishItem(TpWorkflowItem $item, CarbonInterface $publishAt, ?int $actorId, bool $scheduled): TpWorkflowItem
    {
        $resource = $this->resolveResourceOrFail((string) $item->resource_type, (int) $item->resource_id);

        if ($item->pendingRevision instanceof TpRevision) {
            $this->applyRevision($resource, $item->pendingRevision, $publishAt, $actorId);
            $item->pending_revision_id = null;
        } else {
            $this->markPublished($resource, $publishAt, $actorId);
        }

        $item->scheduled_publish_at = null;
        $item->last_transitioned_by = $actorId;
        $item->save();

        $this->audit->log($item, $scheduled ? 'scheduled_publish_executed' : 'published', WorkflowState::Approved, WorkflowState::Approved, $actorId, [
            'published_at' => $publishAt->toAtomString(),
        ]);

        if ($scheduled) {
            event(new WorkflowScheduledPublishExecuted($item->fresh(['owner', 'reviewer', 'approver', 'actor'])));
        }

        return $item->fresh(['owner', 'reviewer', 'approver', 'actor']);
    }

    private function applyRevision(Model $resource, TpRevision $revision, CarbonInterface $publishAt, ?int $actorId): void
    {
        $payload = [
            'title' => (string) $revision->title,
            'slug' => (string) $revision->slug,
            'status' => 'published',
            'layout' => $revision->layout !== null ? (string) $revision->layout : null,
            'blocks' => is_array($revision->blocks) ? $revision->blocks : [],
            'published_at' => $publishAt,
            'updated_by' => $actorId,
        ];

        if ($resource instanceof TpPost) {
            $payload['author_id'] = $revision->author_id !== null ? (int) $revision->author_id : null;
        }

        if (Schema::hasColumn($resource->getTable(), 'editor_driver')) {
            $payload['editor_driver'] = (string) $revision->editor_driver;
        }

        if (Schema::hasColumn($resource->getTable(), 'content')) {
            $payload['content'] = is_array($revision->content) ? $revision->content : null;
        }

        $resource->forceFill($payload)->save();
    }

    private function markPublished(Model $resource, CarbonInterface $publishAt, ?int $actorId): void
    {
        $resource->forceFill([
            'status' => 'published',
            'published_at' => $publishAt,
            'updated_by' => $actorId,
        ])->save();
    }

    private function resolveRecorder(): RevisionRecorder
    {
        throw_if(! class_exists(RevisionRecorder::class) || ! app()->bound(RevisionRecorder::class), InvalidArgumentException::class, 'Workflow requires the revisions plugin to stage published content changes.');

        return app()->make(RevisionRecorder::class);
    }

    private function resolveResourceOrFail(string $resourceType, int $resourceId): Model
    {
        $resource = $this->resources->resolveModel($resourceType, $resourceId);

        throw_unless($resource instanceof Model, InvalidArgumentException::class, 'Unable to resolve the workflow resource.');

        return $resource;
    }

    private function recordStateChange(TpWorkflowItem $item, string $eventType, string $fromState, string $toState, int $actorId): void
    {
        $this->audit->log($item, $eventType, $fromState, $toState, $actorId);
        event(new WorkflowStateChanged($item->fresh(['owner', 'reviewer', 'approver', 'actor']), $eventType, $fromState, $toState));
    }

    private function authorizeManageResource(TpUser $actor, string $resourceType): void
    {
        $capability = $this->resources->capabilityFor($resourceType);
        $this->authorizeCapability($actor, $capability, 'You do not have permission to manage this content.');
    }

    private function authorizeReviewer(TpUser $actor, TpWorkflowItem $item): void
    {
        $this->authorizeCapability($actor, 'review_content', 'You do not have permission to review content.');

        $reviewerId = (int) ($item->reviewer_user_id ?? 0);
        throw_if($reviewerId > 0 && $reviewerId !== (int) $actor->id && ! $actor->isSuperAdmin(), AuthorizationException::class, 'Only the assigned reviewer can request changes.');
    }

    private function authorizeApprover(TpUser $actor, TpWorkflowItem $item): void
    {
        $this->authorizeCapability($actor, 'approve_content', 'You do not have permission to approve content.');

        $approverId = (int) ($item->approver_user_id ?? 0);
        throw_if($approverId > 0 && $approverId !== (int) $actor->id && ! $actor->isSuperAdmin(), AuthorizationException::class, 'Only the assigned approver can approve this item.');
    }

    private function authorizePublisher(TpUser $actor, TpWorkflowItem $item): void
    {
        $this->authorizeCapability($actor, 'publish_content', 'You do not have permission to publish content.');

        $approverId = (int) ($item->approver_user_id ?? 0);
        throw_if($approverId > 0 && $approverId !== (int) $actor->id && ! $actor->isSuperAdmin(), AuthorizationException::class, 'Only the assigned approver can publish this item.');
    }

    private function authorizeCapability(TpUser $actor, string $capability, string $message): void
    {
        if ($actor->isSuperAdmin() || $actor->hasCapability($capability)) {
            return;
        }

        throw new AuthorizationException($message);
    }
}
