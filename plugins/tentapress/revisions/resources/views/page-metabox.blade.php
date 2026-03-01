@php
    $show = ($mode ?? 'edit') === 'edit';
    $revisions = collect();
    $usersById = collect();

    if ($show && class_exists(\TentaPress\Revisions\Services\RevisionHistory::class)) {
        $revisions = app(\TentaPress\Revisions\Services\RevisionHistory::class)->revisionsFor('pages', (int) $page->id, 8);

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
        <div class="tp-metabox__body space-y-4">
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600" data-revisions-autosave-status>
                Autosave idle.
            </div>

            @if ($revisions->isEmpty())
                <div class="tp-muted text-sm">No revisions captured yet.</div>
            @else
                <div class="space-y-3">
                    @foreach ($revisions as $revision)
                        @php
                            $previousRevision = $revisions->get($loop->index + 1);
                            $actor = $usersById->get((int) ($revision->created_by ?? 0));
                            $actorLabel = trim((string) ($actor->name ?? '')) !== '' ? (string) ($actor->name ?? '') : (trim((string) ($actor->email ?? '')) !== '' ? (string) ($actor->email ?? '') : null);
                            $revisionKind = ucfirst((string) ($revision->revision_kind ?? 'manual'));
                        @endphp
                        <article class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="text-sm font-semibold text-slate-900">
                                    {{ $revision->created_at?->diffForHumans() ?? 'Saved just now' }}
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[10px] font-semibold tracking-[0.12em] text-slate-500 uppercase">
                                        {{ $revisionKind }}
                                    </span>
                                    <span class="rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[10px] font-semibold tracking-[0.12em] text-slate-500 uppercase">
                                        {{ $revision->editor_driver ?: 'blocks' }}
                                    </span>
                                </div>
                            </div>
                            <div class="mt-2 space-y-1 text-xs text-slate-600">
                                <div><span class="font-semibold text-slate-700">Status:</span> {{ $revision->status }}</div>
                                <div><span class="font-semibold text-slate-700">Slug:</span> <span class="tp-code">{{ $revision->slug }}</span></div>
                                @if ($actorLabel)
                                    <div><span class="font-semibold text-slate-700">Saved by:</span> {{ $actorLabel }}</div>
                                @endif
                                @if ($revision->restored_from_revision_id)
                                    <div><span class="font-semibold text-slate-700">Restored from:</span> #{{ $revision->restored_from_revision_id }}</div>
                                @endif
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @if ($previousRevision)
                                    <a
                                        href="{{ route('tp.pages.revisions.compare', ['page' => $page->id, 'left' => $previousRevision->id, 'right' => $revision->id]) }}"
                                        class="tp-button-secondary">
                                        Compare
                                    </a>
                                @endif
                                <form
                                    method="POST"
                                    action="{{ route('tp.pages.revisions.restore', ['page' => $page->id, 'revision' => $revision->id]) }}"
                                    data-confirm="Restore this revision? Current content will be replaced.">
                                    @csrf
                                    <button type="submit" class="tp-button-secondary">Restore</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif
