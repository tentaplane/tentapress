@php
    $model = $page ?? $post ?? null;
    $isPage = isset($page);
    $isPost = isset($post);
    $editorTitle = $editorTitle ?? (is_object($model) && trim((string) ($model->title ?? '')) !== '' ? $model->title : 'Untitled');
    $pageDocJson = $pageDocJson ?? (is_array($model?->content ?? null) ? json_encode($model->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : null);
    $pageDocJson = is_string($pageDocJson) && $pageDocJson !== '' ? $pageDocJson : '{"type":"page","content":[]}';
    $editorMode = (bool) ($editorMode ?? false);
    $mode = $mode ?? 'edit';
    $modelId = is_object($model) ? (int) ($model->id ?? 0) : 0;
    $storageKey = $isPost ? 'tp.page_editor.post.'.$modelId : 'tp.page_editor.page.'.$modelId;
@endphp

@once
    @push('head-prepend')
        @tpPluginScripts('tentapress/page-editor')
    @endpush
@endonce

@once
    @push('head')
        @tpPluginStyles('tentapress/page-editor')
    @endpush
@endonce


<div
    class="tp-field space-y-3"
    x-data="tpPageEditor({ initialJson: @js($pageDocJson) })"
    x-init="init()">
    @if ($editorMode && $mode === 'edit' && is_object($model))
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <span
                    class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold tracking-[0.12em] text-slate-500 uppercase">
                    {{ ucfirst((string) ($model->status ?? 'draft')) }}
                </span>
                <span class="hidden text-slate-300 sm:inline">•</span>
                <span class="hidden sm:inline">Editing page</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="submit" form="{{ $isPost ? 'post-form' : 'page-form' }}" class="tp-button-primary">
                    Save changes
                </button>
                @if ($isPost && \Illuminate\Support\Facades\Route::has('tp.public.posts.show'))
                    <a
                        class="tp-button-secondary"
                        href="{{ route('tp.public.posts.show', ['slug' => $model->slug]) }}"
                        target="_blank"
                        rel="noreferrer">
                        View
                    </a>
                @elseif ($isPage)
                    <a
                        class="tp-button-secondary"
                        href="/{{ $model->slug }}"
                        target="_blank"
                        rel="noreferrer">
                        View
                    </a>
                @endif
                @if ($isPage && (string) $model->status === 'draft')
                    <form method="POST" action="{{ route('tp.pages.publish', ['page' => $model->id]) }}">
                        @csrf
                        <button class="tp-button-primary" type="submit">Publish</button>
                    </form>
                @endif
                @if ($isPost && (string) $model->status === 'draft')
                    <form method="POST" action="{{ route('tp.posts.publish', ['post' => $model->id]) }}">
                        @csrf
                        <button class="tp-button-primary" type="submit">Publish</button>
                    </form>
                @endif
                @if ($isPage && (string) $model->status === 'published')
                    <form method="POST" action="{{ route('tp.pages.unpublish', ['page' => $model->id]) }}">
                        @csrf
                        <button class="tp-button-secondary" type="submit">Unpublish</button>
                    </form>
                @endif
                @if ($isPost && (string) $model->status === 'published')
                    <form method="POST" action="{{ route('tp.posts.unpublish', ['post' => $model->id]) }}">
                        @csrf
                        <button class="tp-button-secondary" type="submit">Unpublish</button>
                    </form>
                @endif
                <a
                    href="{{ $isPost ? route('tp.posts.edit', ['post' => $model->id]) : route('tp.pages.edit', ['page' => $model->id]) }}"
                    class="tp-button-secondary">
                    Exit full screen
                </a>
            </div>
        </div>
    @endif

    <label class="tp-label">{{ $editorTitle }}</label>

    <div class="tp-page-editor" x-ref="editor" data-storage-key="{{ $storageKey }}">
        <div class="tp-page-editor__placeholder">Start writing. Type / for commands.</div>
        <div class="tp-page-editor__surface" contenteditable="true" x-ref="surface"></div>
        <div class="tp-page-editor__slash" x-ref="slashMenu"></div>
    </div>

    <div class="tp-page-editor__bubble" x-ref="bubbleMenu">
        <button type="button" class="tp-page-editor__bubble-btn" data-action="bold">Bold</button>
        <button type="button" class="tp-page-editor__bubble-btn" data-action="italic">Italic</button>
        <button type="button" class="tp-page-editor__bubble-btn" data-action="link">Link</button>
    </div>

    <textarea name="page_doc_json" class="hidden" x-ref="hidden" x-model="json"></textarea>
</div>
