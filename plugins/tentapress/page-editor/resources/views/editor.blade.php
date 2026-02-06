@php
    $model = $page ?? $post ?? null;
    $isPage = isset($page);
    $isPost = isset($post);
    $editorTitle = $editorTitle ?? (is_object($model) && trim((string) ($model->title ?? '')) !== '' ? $model->title : 'Untitled');
    $pageDocJson = $pageDocJson ?? (is_array($model?->content ?? null) ? json_encode($model->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : null);
    $pageDocJson = is_string($pageDocJson) && $pageDocJson !== '' ? $pageDocJson : '{"time":0,"blocks":[],"version":"2.28.0"}';
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
    x-data="tpPageEditor({
        initialJson: @js($pageDocJson),
        mediaOptions: @js($mediaOptions ?? []),
        mediaIndexUrl: @js($mediaIndexUrl ?? ''),
    })"
    x-init="init()">
    @if ($editorMode && $mode === 'edit' && is_object($model))
        <div
            class="sticky top-0 z-30 -mx-4 flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-4 py-3 shadow-sm backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
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
                    Exit full-screen
                </a>
            </div>
        </div>
    @endif

    <div class="tp-page-editor" x-ref="editor" data-storage-key="{{ $storageKey }}">
        <div class="tp-page-editor__surface" x-ref="surface"></div>
    </div>

    <textarea name="page_doc_json" class="hidden" x-ref="hidden" x-model="json"></textarea>

    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 py-8"
        x-show="mediaModalOpen"
        x-cloak>
        <div class="w-full max-w-2xl rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div class="text-sm font-semibold text-slate-900">Select an image</div>
                <button type="button" class="tp-button-secondary" @click="closeMediaModal()">Close</button>
            </div>
            <div class="space-y-4 p-5">
                <div class="flex flex-wrap items-center gap-3">
                    <input
                        type="text"
                        class="tp-input flex-1"
                        placeholder="Search media..."
                        x-model="mediaModalSearch" />
                    <a
                        class="tp-button-secondary"
                        x-show="mediaIndexUrl"
                        :href="mediaIndexUrl"
                        target="_blank"
                        rel="noreferrer">
                        Open Media Library
                    </a>
                </div>

                <div class="max-h-80 space-y-2 overflow-y-auto">
                    <template x-if="filteredMediaOptions().length === 0">
                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-6 text-sm text-slate-500">
                            No media available yet. Upload a file in Media Library first.
                        </div>
                    </template>
                    <template x-for="option in filteredMediaOptions()" :key="option.value">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-left text-sm transition hover:border-slate-300 hover:bg-slate-50"
                            @click="chooseMedia(option)">
                            <span class="truncate" x-text="option.label || option.value"></span>
                            <span class="text-xs text-slate-400" x-text="option.mime_type || ''"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
