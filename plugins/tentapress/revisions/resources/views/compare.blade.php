@extends('tentapress-admin::layouts.shell')

@section('title', $resourceLabel.' Revision Compare')

@php
    $leftRevisionKind = (string) ($leftRevision->revision_kind ?? 'manual');
    $rightRevisionKind = (string) ($rightRevision->revision_kind ?? 'manual');
@endphp

@section('content')
    <div class="space-y-6">
        <div class="tp-page-header">
            <div>
                <h1 class="tp-page-title">{{ $resourceLabel }} revision compare</h1>
                <div class="tp-muted mt-1 text-sm">Comparing revision {{ $leftRevision->id }} against {{ $rightRevision->id }}</div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ $backUrl }}" class="tp-button-secondary">Back to editor</a>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="tp-metabox">
                <div class="tp-metabox__title">Earlier revision</div>
                <div class="tp-metabox__body space-y-2 text-sm">
                    <div><span class="font-semibold">ID:</span> {{ $leftRevision->id }}</div>
                    <div><span class="font-semibold">Kind:</span> {{ ucfirst($leftRevisionKind) }}</div>
                    <div><span class="font-semibold">Saved:</span> {{ $leftRevision->created_at?->toDateTimeString() ?? '—' }}</div>
                    <div><span class="font-semibold">Status:</span> {{ $leftRevision->status }}</div>
                    <div><span class="font-semibold">Slug:</span> <span class="tp-code">{{ $leftRevision->slug }}</span></div>
                </div>
            </div>

            <div class="tp-metabox">
                <div class="tp-metabox__title">Later revision</div>
                <div class="tp-metabox__body space-y-2 text-sm">
                    <div><span class="font-semibold">ID:</span> {{ $rightRevision->id }}</div>
                    <div><span class="font-semibold">Kind:</span> {{ ucfirst($rightRevisionKind) }}</div>
                    <div><span class="font-semibold">Saved:</span> {{ $rightRevision->created_at?->toDateTimeString() ?? '—' }}</div>
                    <div><span class="font-semibold">Status:</span> {{ $rightRevision->status }}</div>
                    <div><span class="font-semibold">Slug:</span> <span class="tp-code">{{ $rightRevision->slug }}</span></div>
                </div>
            </div>
        </div>

        <div class="tp-metabox">
            <div class="tp-metabox__title">Changed fields</div>
            <div class="tp-metabox__body space-y-4">
                @if ($changes === [])
                    <div class="tp-muted text-sm">No changes detected between the selected revisions.</div>
                @else
                    @foreach ($changes as $change)
                        <article class="space-y-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div class="text-sm font-semibold text-slate-900">{{ $change['label'] }}</div>
                            <div class="grid gap-4 lg:grid-cols-2">
                                <div>
                                    <div class="mb-1 text-xs font-semibold tracking-[0.12em] text-slate-500 uppercase">Earlier</div>
                                    <pre class="overflow-x-auto rounded-lg border border-slate-200 bg-white p-3 text-xs text-slate-700">{{ $change['left'] !== '' ? $change['left'] : '—' }}</pre>
                                </div>
                                <div>
                                    <div class="mb-1 text-xs font-semibold tracking-[0.12em] text-slate-500 uppercase">Later</div>
                                    <pre class="overflow-x-auto rounded-lg border border-slate-200 bg-white p-3 text-xs text-slate-700">{{ $change['right'] !== '' ? $change['right'] : '—' }}</pre>
                                </div>
                            </div>
                        </article>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
@endsection
