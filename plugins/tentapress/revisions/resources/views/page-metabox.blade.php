@php
    $show = ($mode ?? 'edit') === 'edit';
    $revisions = collect();
    $usersById = collect();

    if ($show && class_exists(\TentaPress\Revisions\Services\RevisionHistory::class)) {
        $revisions = app(\TentaPress\Revisions\Services\RevisionHistory::class)->revisionsFor('pages', (int) $page->id, 5);

        $userIds = $revisions
            ->pluck('created_by')
            ->filter(static fn ($value): bool => (int) $value > 0)
            ->map(static fn ($value): int => (int) $value)
            ->unique()
            ->values();

        if ($userIds->isNotEmpty() && class_exists(\TentaPress\Users\Models\TpUser::class) && \Illuminate\Support\Facades\Schema::hasTable('tp_users')) {
            $usersById = \TentaPress\Users\Models\TpUser::query()
                ->whereIn('id', $userIds->all())
                ->get(['id', 'name', 'email'])
                ->keyBy('id');
        }
    }
@endphp

@if ($show)
    <div class="tp-metabox">
        <div class="tp-metabox__title">Revisions</div>
        <div class="tp-metabox__body space-y-3 text-sm">
            <div class="rounded-md border border-black/10 bg-[var(--tp-surface-soft)]/70 px-3 py-2 text-xs text-black/60" data-revisions-autosave-status>
                Autosave idle.
            </div>

            @if ($revisions->isEmpty())
                <div class="tp-muted text-sm">No revisions captured yet.</div>
            @else
                <div class="space-y-2">
                    @foreach ($revisions as $revision)
                        @php
                            $previousRevision = $revisions->get($loop->index + 1);
                            $actor = $usersById->get((int) ($revision->created_by ?? 0));
                            $actorLabel = trim((string) ($actor->name ?? '')) !== '' ? (string) ($actor->name ?? '') : (trim((string) ($actor->email ?? '')) !== '' ? (string) ($actor->email ?? '') : null);
                            $revisionKind = ucfirst((string) ($revision->revision_kind ?? 'manual'));
                        @endphp
                        <article class="rounded-md border border-black/10 bg-[var(--tp-surface-soft)]/45 p-2.5">
                            <div class="flex flex-wrap items-start justify-between gap-x-3 gap-y-1">
                                <div class="font-semibold text-black/85">
                                    {{ $revision->created_at?->diffForHumans() ?? 'Saved just now' }}
                                </div>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="tp-badge tp-badge-neutral text-[10px]">
                                        {{ $revisionKind }}
                                    </span>
                                    <span class="tp-badge tp-badge-neutral text-[10px]">
                                        {{ $revision->editor_driver ?: 'blocks' }}
                                    </span>
                                </div>
                            </div>
                            <div class="mt-2 space-y-1 text-xs">
                                <div>
                                    <span class="tp-muted">Status:</span>
                                    <span>{{ $revision->status }}</span>
                                </div>
                                <div>
                                    <span class="tp-muted">Slug:</span>
                                    <span class="tp-code">{{ $revision->slug }}</span>
                                </div>
                                @if ($actorLabel)
                                    <div>
                                        <span class="tp-muted">Saved by:</span>
                                        <span>{{ $actorLabel }}</span>
                                    </div>
                                @endif
                                @if ($revision->restored_from_revision_id)
                                    <div>
                                        <span class="tp-muted">Restored from:</span>
                                        <span>#{{ $revision->restored_from_revision_id }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @if ($previousRevision)
                                    <a
                                        href="{{ route('tp.pages.revisions.compare', ['page' => $page->id, 'left' => $previousRevision->id, 'right' => $revision->id]) }}"
                                        class="tp-button-secondary min-h-0 px-2.5 py-1 text-xs">
                                        Compare
                                    </a>
                                @endif
                                <form
                                    method="POST"
                                    action="{{ route('tp.pages.revisions.restore', ['page' => $page->id, 'revision' => $revision->id]) }}"
                                    data-confirm="Restore this revision? Current content will be replaced.">
                                    @csrf
                                    <button type="submit" class="tp-button-secondary min-h-0 px-2.5 py-1 text-xs">Restore</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif
