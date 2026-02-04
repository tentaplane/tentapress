@extends('tentapress-admin::layouts.shell')

@php
    $editorMode = (bool) ($editorMode ?? false);
    $customEditorView = app()->bound('tp.pages.editor.view') ? resolve('tp.pages.editor.view') : null;
    $customEditorView = is_string($customEditorView) && view()->exists($customEditorView) ? $customEditorView : null;
    $editorLabel = $customEditorView ? 'Page Editor' : 'Blocks Editor';
@endphp

@if ($editorMode)
    @section('shell_fullscreen', '1')
    @section('body_class', 'bg-slate-100')
@endif

@section('title', $editorMode ? $editorLabel : ($mode === 'create' ? 'Add New Page' : 'Edit Page'))

@section('content')
    <div class="tp-editor {{ $editorMode ? 'space-y-0 px-4 pt-0 pb-6 sm:px-6 lg:px-8' : 'space-y-6' }}">
        @if (! $editorMode)
            <div class="tp-page-header">
                <div class="{{ $editorMode ? 'space-y-1' : '' }}">
                    <h1 class="tp-page-title">
                        {{ $mode === 'create' ? 'Add New Page' : 'Edit Page' }}
                    </h1>
                </div>

                @if ($mode === 'edit')
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('tp.pages.editor', ['page' => $page->id]) }}" class="tp-button-secondary">
                            Full screen editor
                        </a>
                    </div>
                @endif
            </div>
        @endif

        <div class="{{ $editorMode ? 'lg:grid-cols-1' : 'lg:grid-cols-4' }} grid grid-cols-1 gap-6">
            <div class="{{ $editorMode ? 'lg:col-span-1' : 'lg:col-span-3' }} space-y-6">
                <div class="{{ $editorMode ? '' : 'tp-metabox' }}">
                    <div class="{{ $editorMode ? 'space-y-4' : 'tp-metabox__body space-y-4' }}">
                        <form
                            method="POST"
                            action="{{ $mode === 'create' ? route('tp.pages.store') : route('tp.pages.update', ['page' => $page->id]) }}"
                            class="space-y-4"
                            id="page-form">
                            @csrf
                            @if ($mode === 'edit')
                                @method('PUT')
                            @endif

                            @if ($editorMode)
                                <input type="hidden" name="title" value="{{ old('title', $page->title) }}" />
                                <input type="hidden" name="slug" value="{{ old('slug', $page->slug) }}" />
                                <input type="hidden" name="layout" value="{{ old('layout', $page->layout) }}" />
                                <input type="hidden" name="return_to" value="editor" />
                            @else
                                <div
                                    class="space-y-4"
                                    x-data="{
                                        title: @js(old('title', $page->title)),
                                        slug: @js(old('slug', $page->slug)),
                                        titleTouched: false,
                                        slugTouched: false,
                                        isSlugValid() {
                                            return this.slug.trim() === '' || /^[a-z0-9-]+$/.test(this.slug)
                                        },
                                    }">
                                    <div class="tp-field">
                                        <label class="tp-label">Title</label>
                                        <input
                                            name="title"
                                            class="tp-input"
                                            value="{{ old('title', $page->title) }}"
                                            x-model="title"
                                            @blur="titleTouched = true"
                                            required />
                                        <div class="tp-help">Required.</div>
                                        <div
                                            class="tp-help text-red-600"
                                            x-show="titleTouched && title.trim().length === 0"
                                            x-cloak>
                                            Title is required.
                                        </div>
                                    </div>

                                    @php
                                        $themeLayouts = is_array($themeLayouts ?? null) ? $themeLayouts : [];
                                        $currentLayout = old('layout', $page->layout);
                                        $currentLayout = is_string($currentLayout) ? $currentLayout : '';
                                        $currentLayout = $currentLayout !== '' ? $currentLayout : 'default';
                                    @endphp

                                    <div class="grid gap-4 lg:grid-cols-2">
                                        <div class="tp-field">
                                            <label class="tp-label">Slug</label>
                                            <input
                                                name="slug"
                                                class="tp-input"
                                                value="{{ old('slug', $page->slug) }}"
                                                x-model="slug"
                                                @blur="slugTouched = true"
                                                placeholder="auto-generated if blank (on create)"
                                                pattern="[a-z0-9-]+"
                                                title="Lowercase, numbers, and dashes only."
                                                {{ $mode === 'create' ? '' : 'required' }} />
                                            <div class="tp-help">Lowercase, numbers, and dashes only.</div>
                                            <div
                                                class="tp-help text-red-600"
                                                x-show="slugTouched && slug.trim() !== '' && ! isSlugValid()"
                                                x-cloak>
                                                Use only lowercase letters, numbers, and dashes.
                                            </div>
                                        </div>

                                        <div class="tp-field">
                                            <label class="tp-label">Layout</label>

                                            @if (count($themeLayouts) > 0)
                                                <select name="layout" class="tp-select">
                                                    @foreach ($themeLayouts as $layout)
                                                        @php
                                                            $key = isset($layout['key']) ? (string) $layout['key'] : '';
                                                            $label = isset($layout['label']) ? (string) $layout['label'] : $key;
                                                        @endphp

                                                        @if ($key !== '')
                                                            <option
                                                                value="{{ $key }}"
                                                                @selected($currentLayout === $key)>
                                                                {{ $label }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <div class="tp-help">Layouts come from the active theme manifest.</div>
                                            @else
                                                <input
                                                    name="layout"
                                                    class="tp-input"
                                                    value="{{ $currentLayout }}"
                                                    placeholder="default" />
                                                <div class="tp-help">
                                                    No theme layouts
                                                    found{{ ! empty($hasTheme) ? '' : ' (no active theme)' }} — using
                                                    free text key.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($customEditorView)
                                @include($customEditorView, [
                                    'page' => $page,
                                    'editorTitle' => $editorMode ? (trim((string) ($page->title ?? '')) !== '' ? $page->title : 'Untitled Page') : null,
                                    'pageDocJson' => $pageDocJson ?? null,
                                    'editorMode' => $editorMode,
                                    'mode' => $mode,
                                ])
                            @else
                                @component('tentapress-blocks::editor', [
                                    'blocksEditorMode' => $editorMode,
                                    'editorTitle' => $editorMode ? (trim((string) ($page->title ?? '')) !== '' ? $page->title : 'Untitled Page') : null,
                                    'blocksJson' => $blocksJson,
                                    'blockDefinitions' => $blockDefinitions ?? [],
                                    'mediaOptions' => $mediaOptions ?? [],
                                    'mediaIndexUrl' => \Illuminate\Support\Facades\Route::has('tp.media.index') ? route('tp.media.index') : '',
                                ])
                                    @if ($editorMode && $mode === 'edit')
                                        @slot('header')
                                            <div
                                                class="sticky top-0 z-30 -mx-4 flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-4 py-3 shadow-sm backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                                                <div class="min-w-0">
                                                    <div class="truncate text-base font-semibold text-slate-900">
                                                        {{ trim((string) ($page->title ?? '')) !== '' ? $page->title : 'Untitled Page' }}
                                                    </div>
                                                    <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                                    <span
                                                        class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold tracking-[0.12em] text-slate-500 uppercase">
                                                        {{ ucfirst($page->status) }}
                                                    </span>
                                                    <span class="hidden text-slate-300 sm:inline">•</span>
                                                    <span class="hidden sm:inline">Editing blocks</span>
                                                    </div>
                                                </div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <button type="submit" form="page-form" class="tp-button-primary">
                                                        Save changes
                                                    </button>
                                                    <a
                                                        class="tp-button-secondary"
                                                        href="/{{ $page->slug }}"
                                                        target="_blank"
                                                        rel="noreferrer">
                                                        View
                                                    </a>
                                                    @if ($page->status === 'draft')
                                                        <form
                                                            method="POST"
                                                            action="{{ route('tp.pages.publish', ['page' => $page->id]) }}">
                                                            @csrf
                                                            <button class="tp-button-primary" type="submit">Publish</button>
                                                        </form>
                                                    @endif
                                
                                                    @if ($page->status === 'published')
                                                        <form
                                                            method="POST"
                                                            action="{{ route('tp.pages.unpublish', ['page' => $page->id]) }}">
                                                            @csrf
                                                            <button class="tp-button-secondary" type="submit">Unpublish</button>
                                                        </form>
                                                    @endif
                                
                                                    <a
                                                        href="{{ route('tp.pages.edit', ['page' => $page->id]) }}"
                                                        class="tp-button-secondary">
                                                        Exit full screen
                                                    </a>
                                                </div>
                                            </div>
                                        @endslot
                                    @endif
                                @endcomponent
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            @if (! $editorMode)
                <div class="space-y-6 lg:sticky lg:top-6 lg:self-start">
                    <div class="tp-metabox">
                        <div class="tp-metabox__title">Status</div>
                        <div class="tp-metabox__body space-y-4 text-sm">
                            <div class="space-y-2">
                                <div>
                                    <span class="tp-muted">Status:</span>
                                    <span class="font-semibold">{{ ucfirst($page->status) }}</span>
                                </div>
                                <div>
                                    <span class="tp-muted">Published:</span>
                                    <span class="tp-code">{{ $page->published_at?->toDateTimeString() ?? '—' }}</span>
                                </div>
                                <div>
                                    <span class="tp-muted">Updated:</span>
                                    <span class="tp-code">{{ $page->updated_at?->toDateTimeString() ?? '—' }}</span>
                                </div>
                            </div>

                            <div class="tp-divider"></div>

                            <div class="space-y-2">
                                <button type="submit" form="page-form" class="tp-button-primary w-full justify-center">
                                    {{ $mode === 'create' ? 'Create Page' : 'Save Changes' }}
                                </button>

                                @if ($mode === 'edit')
                                    <a
                                        class="tp-button-secondary w-full justify-center"
                                        href="/{{ $page->slug }}"
                                        target="_blank"
                                        rel="noreferrer">
                                        View
                                    </a>
                                @endif

                                @if ($mode === 'edit' && $page->status === 'draft')
                                    <form
                                        method="POST"
                                        action="{{ route('tp.pages.publish', ['page' => $page->id]) }}">
                                        @csrf
                                        <button class="tp-button-primary w-full justify-center" type="submit">
                                            Publish
                                        </button>
                                    </form>
                                @endif

                                @if ($mode === 'edit' && $page->status === 'published')
                                    <form
                                        method="POST"
                                        action="{{ route('tp.pages.unpublish', ['page' => $page->id]) }}">
                                        @csrf
                                        <button class="tp-button-secondary w-full justify-center" type="submit">
                                            Unpublish
                                        </button>
                                    </form>
                                @endif

                                @if ($mode === 'edit')
                                    <form
                                        method="POST"
                                        action="{{ route('tp.pages.destroy', ['page' => $page->id]) }}"
                                        onsubmit="return confirm('Delete this page? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="tp-button-danger w-full justify-center"
                                            aria-label="Delete page">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    @includeIf('tentapress-seo::page-metabox', ['page' => $page, 'mode' => $mode])
                </div>
            @endif
        </div>
    </div>
@endsection
