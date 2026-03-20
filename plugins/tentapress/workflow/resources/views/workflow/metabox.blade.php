@php
    /** @var \TentaPress\Workflow\Models\TpWorkflowItem|null $workflowItem */
    $workflowItem = $workflowItem ?? null;
    $workflowUsers = is_array($workflowUsers ?? null) ? $workflowUsers : [];
    $workflowResourceType = (string) ($workflowResourceType ?? '');
    $resourceId = (int) (($page->id ?? $post->id ?? 0));
    $currentUser = auth()->user();
    $canManageWorkflowResource = is_object($currentUser) && method_exists($currentUser, 'hasCapability')
        ? ($currentUser->isSuperAdmin() || $currentUser->hasCapability($workflowResourceType === 'posts' ? 'manage_posts' : 'manage_pages'))
        : false;
    $canReviewWorkflow = is_object($currentUser) && method_exists($currentUser, 'hasCapability')
        ? ($currentUser->isSuperAdmin() || $currentUser->hasCapability('review_content'))
        : false;
    $canApproveWorkflow = is_object($currentUser) && method_exists($currentUser, 'hasCapability')
        ? ($currentUser->isSuperAdmin() || $currentUser->hasCapability('approve_content'))
        : false;
    $canPublishWorkflow = is_object($currentUser) && method_exists($currentUser, 'hasCapability')
        ? ($currentUser->isSuperAdmin() || $currentUser->hasCapability('publish_content'))
        : false;
    $routeUrl = static function (string $name, string $path, array $parameters = []): string {
        return \Illuminate\Support\Facades\Route::has($name) ? route($name, $parameters) : url($path);
    };
@endphp

@if ($workflowItem)
    <div class="tp-metabox">
        <div class="tp-metabox__title">Workflow</div>
        <div class="tp-metabox__body space-y-4 text-sm">
            <div class="space-y-2">
                <div>
                    <span class="tp-muted">Editorial state:</span>
                    <span class="font-semibold">{{ str_replace('_', ' ', ucfirst((string) $workflowItem->editorial_state)) }}</span>
                </div>
                <div>
                    <span class="tp-muted">Next action:</span>
                    <span>{{ $workflowItem->nextActionLabel() }}</span>
                </div>
                <div>
                    <span class="tp-muted">Working copy:</span>
                    <span>{{ $workflowItem->hasPendingRevision() ? 'Staged from revisions' : 'None' }}</span>
                </div>
                <div>
                    <span class="tp-muted">Scheduled:</span>
                    <span class="tp-code">{{ $workflowItem->scheduled_publish_at?->toDateTimeString() ?? '—' }}</span>
                </div>
            </div>

            <div class="tp-divider"></div>

            @if ($canManageWorkflowResource)
                <form method="POST" action="{{ $routeUrl('tp.workflow.assign', '/admin/workflow/'.$workflowResourceType.'/'.$resourceId.'/assign', ['resourceType' => $workflowResourceType, 'resourceId' => $resourceId]) }}" class="space-y-3">
                    @csrf

                    <div class="tp-field">
                        <label class="tp-label" for="workflow-owner-{{ $resourceId }}">Owner</label>
                        <select id="workflow-owner-{{ $resourceId }}" name="owner_user_id" class="tp-select">
                            <option value="">Unassigned</option>
                            @foreach ($workflowUsers as $workflowUser)
                                <option value="{{ $workflowUser->id }}" @selected((int) ($workflowItem->owner_user_id ?? 0) === (int) $workflowUser->id)>
                                    {{ $workflowUser->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="tp-field">
                        <label class="tp-label" for="workflow-reviewer-{{ $resourceId }}">Reviewer</label>
                        <select id="workflow-reviewer-{{ $resourceId }}" name="reviewer_user_id" class="tp-select">
                            <option value="">Unassigned</option>
                            @foreach ($workflowUsers as $workflowUser)
                                <option value="{{ $workflowUser->id }}" @selected((int) ($workflowItem->reviewer_user_id ?? 0) === (int) $workflowUser->id)>
                                    {{ $workflowUser->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="tp-field">
                        <label class="tp-label" for="workflow-approver-{{ $resourceId }}">Approver</label>
                        <select id="workflow-approver-{{ $resourceId }}" name="approver_user_id" class="tp-select">
                            <option value="">Unassigned</option>
                            @foreach ($workflowUsers as $workflowUser)
                                <option value="{{ $workflowUser->id }}" @selected((int) ($workflowItem->approver_user_id ?? 0) === (int) $workflowUser->id)>
                                    {{ $workflowUser->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="tp-button-secondary w-full justify-center">Save workflow assignments</button>
                </form>
            @endif

            <div class="tp-divider"></div>

            <div class="space-y-2">
                @if ($canManageWorkflowResource && in_array((string) $workflowItem->editorial_state, ['draft', 'changes_requested'], true))
                    <form method="POST" action="{{ $routeUrl('tp.workflow.submit', '/admin/workflow/'.$workflowResourceType.'/'.$resourceId.'/submit', ['resourceType' => $workflowResourceType, 'resourceId' => $resourceId]) }}">
                        @csrf
                        <button type="submit" class="tp-button-primary w-full justify-center">Submit for review</button>
                    </form>
                @endif

                @if ($canReviewWorkflow && (string) $workflowItem->editorial_state === 'in_review')
                    <form method="POST" action="{{ $routeUrl('tp.workflow.changes', '/admin/workflow/'.$workflowResourceType.'/'.$resourceId.'/changes', ['resourceType' => $workflowResourceType, 'resourceId' => $resourceId]) }}">
                        @csrf
                        <button type="submit" class="tp-button-secondary w-full justify-center">Request changes</button>
                    </form>
                @endif

                @if ($canApproveWorkflow && (string) $workflowItem->editorial_state === 'in_review')
                    <form method="POST" action="{{ $routeUrl('tp.workflow.approve', '/admin/workflow/'.$workflowResourceType.'/'.$resourceId.'/approve', ['resourceType' => $workflowResourceType, 'resourceId' => $resourceId]) }}">
                        @csrf
                        <button type="submit" class="tp-button-primary w-full justify-center">Approve</button>
                    </form>
                @endif

                @if ($canApproveWorkflow && (string) $workflowItem->editorial_state === 'approved')
                    <form method="POST" action="{{ $routeUrl('tp.workflow.revoke', '/admin/workflow/'.$workflowResourceType.'/'.$resourceId.'/revoke', ['resourceType' => $workflowResourceType, 'resourceId' => $resourceId]) }}">
                        @csrf
                        <button type="submit" class="tp-button-secondary w-full justify-center">Revoke approval</button>
                    </form>
                @endif

                @if ($canPublishWorkflow && (string) $workflowItem->editorial_state === 'approved')
                    <form method="POST" action="{{ $routeUrl('tp.workflow.publish', '/admin/workflow/'.$workflowResourceType.'/'.$resourceId.'/publish', ['resourceType' => $workflowResourceType, 'resourceId' => $resourceId]) }}">
                        @csrf
                        <button type="submit" class="tp-button-primary w-full justify-center">Publish now</button>
                    </form>

                    <form method="POST" action="{{ $routeUrl('tp.workflow.schedule', '/admin/workflow/'.$workflowResourceType.'/'.$resourceId.'/schedule', ['resourceType' => $workflowResourceType, 'resourceId' => $resourceId]) }}" class="space-y-2">
                        @csrf
                        <div class="tp-field">
                            <label class="tp-label" for="workflow-scheduled-{{ $resourceId }}">Schedule publish</label>
                            <input
                                id="workflow-scheduled-{{ $resourceId }}"
                                type="datetime-local"
                                name="scheduled_publish_at"
                                class="tp-input"
                                value="{{ $workflowItem->scheduled_publish_at?->format('Y-m-d\\TH:i') }}" />
                        </div>
                        <button type="submit" class="tp-button-secondary w-full justify-center">Save schedule</button>
                    </form>
                @endif

                <a href="{{ $routeUrl('tp.workflow.index', '/admin/workflow') }}" class="tp-button-link">Open workflow queue</a>
            </div>
        </div>
    </div>
@endif
