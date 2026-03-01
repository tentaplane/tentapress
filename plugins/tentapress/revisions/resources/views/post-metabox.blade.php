@php
    $show = ($mode ?? 'edit') === 'edit';
    $revisions = collect();
    $usersById = collect();

    if ($show && class_exists(\TentaPress\Revisions\Models\TpRevision::class)) {
        $revisions = \TentaPress\Revisions\Models\TpRevision::query()
            ->where('resource_type', 'posts')
            ->where('resource_id', (int) $post->id)
            ->latest('id')
            ->limit(8)
            ->get();

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
            @if ($revisions->isEmpty())
                <div class="tp-muted text-sm">No revisions captured yet.</div>
            @else
                <div class="space-y-3">
                    @foreach ($revisions as $revision)
                        @php
                            $actor = $usersById->get((int) ($revision->created_by ?? 0));
                            $actorLabel = trim((string) ($actor->name ?? '')) !== '' ? (string) $actor->name : (trim((string) ($actor->email ?? '')) !== '' ? (string) $actor->email : null);
                        @endphp
                        <article class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="text-sm font-semibold text-slate-900">
                                    {{ $revision->created_at?->diffForHumans() ?? 'Saved just now' }}
                                </div>
                                <span class="rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[10px] font-semibold tracking-[0.12em] text-slate-500 uppercase">
                                    {{ $revision->editor_driver ?: 'blocks' }}
                                </span>
                            </div>
                            <div class="mt-2 space-y-1 text-xs text-slate-600">
                                <div><span class="font-semibold text-slate-700">Status:</span> {{ $revision->status }}</div>
                                <div><span class="font-semibold text-slate-700">Slug:</span> <span class="tp-code">{{ $revision->slug }}</span></div>
                                @if ($revision->author_id)
                                    <div><span class="font-semibold text-slate-700">Author ID:</span> {{ $revision->author_id }}</div>
                                @endif
                                @if ($actorLabel)
                                    <div><span class="font-semibold text-slate-700">Saved by:</span> {{ $actorLabel }}</div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="tp-muted text-xs">Restore and diff flows can build on these snapshots.</div>
            @endif
        </div>
    </div>
@endif
