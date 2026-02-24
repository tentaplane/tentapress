@php
    $model = $page ?? $post ?? null;
    $isPage = isset($page);
    $isPost = isset($post);
    $editorTitle = $editorTitle ?? (is_object($model) && trim((string) ($model->title ?? '')) !== '' ? $model->title : 'Untitled');
    $blocksJson = $blocksJson ?? (is_array($model?->blocks ?? null) ? json_encode($model->blocks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '[]');
    $blocksJson = is_string($blocksJson) && $blocksJson !== '' ? $blocksJson : '[]';
    $resource = $isPost ? 'posts' : 'pages';
    $editorMode = (bool) ($editorMode ?? false);
    $mode = $mode ?? 'edit';
    $modelId = is_object($model) ? (int) ($model->id ?? 0) : 0;
    $storageKey = $isPost ? 'tp.builder.post.'.$modelId : 'tp.builder.page.'.$modelId;
    $builderConfig = [
        'initialJson' => $blocksJson,
        'resource' => $resource,
        'snapshotEndpoint' => \Illuminate\Support\Facades\Route::has('tp.builder.snapshots.store') ? route('tp.builder.snapshots.store') : '',
        'storageKey' => $storageKey,
        'hiddenFieldId' => 'tp-builder-json-field',
        'definitions' => is_array($blockDefinitions ?? null) ? $blockDefinitions : [],
        'mediaOptions' => is_array($mediaOptions ?? null) ? $mediaOptions : [],
    ];
@endphp

@once
    @push('head-prepend')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @tpPluginScripts('tentapress/builder')
    @endpush
@endonce

@once
    @push('head')
        @tpPluginStyles('tentapress/builder')
    @endpush
@endonce

<div class="tp-builder-field space-y-3">
    @if ($editorMode && $mode === 'edit' && is_object($model))
        <div
            class="sticky top-0 z-30 -mx-4 flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-4 py-3 shadow-sm backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
            <div class="min-w-0">
                <div class="truncate text-base font-semibold text-slate-900">{{ $editorTitle }}</div>
                <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                    <span
                        class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold tracking-[0.12em] text-slate-500 uppercase">
                        {{ ucfirst((string) ($model->status ?? 'draft')) }}
                    </span>
                    <span class="hidden text-slate-300 sm:inline">•</span>
                    <span class="hidden sm:inline">Editing with Visual Builder</span>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="submit" form="{{ $isPost ? 'post-form' : 'page-form' }}" class="tp-button-primary">Save changes</button>
                @if ($isPost && \Illuminate\Support\Facades\Route::has('tp.public.posts.show'))
                    <a class="tp-button-secondary" href="{{ route('tp.public.posts.show', ['slug' => $model->slug]) }}" target="_blank" rel="noreferrer">View</a>
                @elseif ($isPage)
                    <a class="tp-button-secondary" href="/{{ $model->slug }}" target="_blank" rel="noreferrer">View</a>
                @endif
                <a href="{{ $isPost ? route('tp.posts.edit', ['post' => $model->id]) : route('tp.pages.edit', ['page' => $model->id]) }}" class="tp-button-secondary">
                    Exit full-screen
                </a>
            </div>
        </div>
    @endif

    <div
        id="tp-builder-root"
        data-config='@json($builderConfig)'>
    </div>

    <textarea id="tp-builder-json-field" name="blocks_json" class="hidden">{{ $blocksJson }}</textarea>
</div>
