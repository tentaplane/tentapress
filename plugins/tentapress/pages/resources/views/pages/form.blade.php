@extends('tentapress-admin::layouts.shell')

@php
    $editorMode = (bool) ($editorMode ?? false);
    $driverRegistry = app()->bound(\TentaPress\System\Editor\EditorDriverRegistry::class)
        ? app(\TentaPress\System\Editor\EditorDriverRegistry::class)
        : null;
    $editorDrivers = $driverRegistry instanceof \TentaPress\System\Editor\EditorDriverRegistry
        ? $driverRegistry->allFor('pages')
        : [];
    if ($editorDrivers === []) {
        $editorDrivers = [
            new \TentaPress\System\Editor\EditorDriverDefinition(
                id: 'blocks',
                label: 'Blocks Builder',
                description: 'Structured sections and fields.',
                storage: 'blocks',
                usesBlocksEditor: true,
                sortOrder: 10,
            ),
        ];
    }
    $editorDriverMap = [];
    foreach ($editorDrivers as $definition) {
        if (! $definition instanceof \TentaPress\System\Editor\EditorDriverDefinition) {
            continue;
        }

        $editorDriverMap[$definition->id] = $definition;
    }

    $editorDriver = old('editor_driver', $formEditorDriver);
    $editorDriver = is_string($editorDriver) && isset($editorDriverMap[$editorDriver]) ? $editorDriver : 'blocks';
    if (! isset($editorDriverMap[$editorDriver])) {
        $editorDriver = array_key_first($editorDriverMap) ?? 'blocks';
    }

    $selectedDriver = $editorDriverMap[$editorDriver] ?? null;
    $selectedEditorView = $selectedDriver?->viewFor('pages');
    $selectedEditorView = is_string($selectedEditorView) && view()->exists($selectedEditorView) ? $selectedEditorView : null;
    $usesBlocksEditor = $selectedDriver?->usesBlocksEditor ?? true;
    $editorLabel = $selectedDriver?->label ?? 'Blocks Builder';
    $isBuilderDriver = ($selectedDriver?->id ?? '') === 'builder';
    $canRenderSelectedEditorInline = $editorMode || ! $isBuilderDriver;
    $formTitle = is_string($formTitle ?? null) ? $formTitle : (string) ($page->title ?? '');
    $formSlug = is_string($formSlug ?? null) ? $formSlug : (string) ($page->slug ?? '');
    $formLayout = is_string($formLayout ?? null) ? $formLayout : (string) ($page->layout ?? '');
    $formEditorDriver = is_string($formEditorDriver ?? null) ? $formEditorDriver : (string) ($page->editor_driver ?? 'blocks');
    $revisionsEnabled = (bool) ($revisionsPluginEnabled ?? false);
    $revisionsEnabled = $revisionsEnabled && ($mode === 'edit')
        && \Illuminate\Support\Facades\Route::has('tp.pages.revisions.autosave')
        && view()->exists('tentapress-revisions::page-metabox');
    $taxonomiesPluginEnabled = (bool) ($taxonomiesPluginEnabled ?? true);
    $loadedAutosaveAt = is_object($loadedAutosave ?? null) && isset($loadedAutosave->created_at)
        ? $loadedAutosave->created_at
        : null;
@endphp

@if ($editorMode)
    @section('shell_fullscreen', '1')
    @section('body_class', 'bg-slate-100')
@endif

@section('title', $editorMode ? $editorLabel : ($mode === 'create' ? 'Create Page' : 'Edit Page'))

@section('content')
    <div class="tp-editor {{ $editorMode ? 'space-y-0 px-4 pt-0 pb-6 sm:px-6 lg:px-8' : 'space-y-6' }}">
        @if (! $editorMode)
            <div class="tp-page-header">
                <div class="{{ $editorMode ? 'space-y-1' : '' }}">
                    <h1 class="tp-page-title">
                        {{ $mode === 'create' ? 'Create Page' : 'Edit Page' }}
                    </h1>
                </div>

                @if ($mode === 'edit')
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('tp.pages.editor', ['page' => $page->id]) }}" class="tp-button-secondary">
                            Full-screen editor
                        </a>
                    </div>
                @endif
            </div>
        @endif

        @if ($revisionsEnabled && $loadedAutosaveAt)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                Loaded autosave draft from {{ $loadedAutosaveAt?->diffForHumans() ?? 'just now' }}.
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
                            id="page-form"
                            @if ($revisionsEnabled)
                                data-revisions-autosave-url="{{ route('tp.pages.revisions.autosave', ['page' => $page->id]) }}"
                            @endif
                            @if ($mode === 'edit' && count($editorDriverMap) > 1)
                                data-editor-switch-form="1"
                                data-editor-driver-current="{{ $editorDriver }}"
                            @endif>
                            @csrf
                            @if ($mode === 'edit')
                                @method('PUT')
                            @endif

                            @if ($editorMode)
                                <input type="hidden" name="title" value="{{ old('title', $formTitle) }}" />
                                <input type="hidden" name="slug" value="{{ old('slug', $formSlug) }}" />
                                <input type="hidden" name="layout" value="{{ old('layout', $formLayout) }}" />
                                <input type="hidden" name="editor_driver" value="{{ $editorDriver }}" />
                                <input type="hidden" name="return_to" value="editor" />
                            @else
                                <input type="hidden" name="return_to" value="" data-editor-return-to />
                                <div
                                    class="space-y-4"
                                    x-data="{
                                        title: @js(old('title', $formTitle)),
                                        slug: @js(old('slug', $formSlug)),
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
                                            value="{{ old('title', $formTitle) }}"
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
                                        $currentLayout = old('layout', $formLayout);
                                        $currentLayout = is_string($currentLayout) ? $currentLayout : '';
                                        $currentLayout = $currentLayout !== '' ? $currentLayout : 'default';
                                    @endphp

                                    <div class="grid gap-4 lg:grid-cols-2">
                                        <div class="tp-field">
                                            <label class="tp-label">URL slug</label>
                                            <input
                                                name="slug"
                                                class="tp-input"
                                                value="{{ old('slug', $formSlug) }}"
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
                                                <div class="tp-help">Layouts come from your active theme.</div>
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

                                    @if (count($editorDriverMap) > 1)
                                        <div class="tp-field">
                                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                @foreach ($editorDriverMap as $driverId => $driverDefinition)
                                                    <label class="cursor-pointer">
                                                        <input
                                                            type="radio"
                                                            name="editor_driver"
                                                            value="{{ $driverId }}"
                                                            class="sr-only peer"
                                                            data-editor-switch-radio
                                                            data-editor-label="{{ $driverDefinition->label }}"
                                                            @checked($editorDriver === $driverId) />
                                                        <div class="rounded-xl border border-slate-200 bg-white p-3 transition peer-checked:border-slate-900 peer-checked:ring-2 peer-checked:ring-slate-200">
                                                            <div class="text-sm font-semibold text-slate-900">{{ $driverDefinition->label }}</div>
                                                            <div class="mt-1 text-xs text-slate-500">{{ $driverDefinition->description }}</div>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <div class="tp-help">Choose the editing experience for this page.</div>
                                        </div>
                                    @else
                                        <input type="hidden" name="editor_driver" value="{{ $editorDriver }}" />
                                    @endif
                                </div>
                            @endif

                            @if (! $usesBlocksEditor && $selectedEditorView && $canRenderSelectedEditorInline)
                                @include($selectedEditorView, [
                                    'page' => $page,
                                    'editorTitle' => $editorMode ? (trim((string) ($page->title ?? '')) !== '' ? $page->title : 'Untitled Page') : null,
                                    'pageDocJson' => $pageDocJson ?? null,
                                    'blocksJson' => $blocksJson ?? '[]',
                                    'blockDefinitions' => $blockDefinitions ?? [],
                                    'mediaOptions' => $mediaOptions ?? [],
                                    'mediaIndexUrl' => \Illuminate\Support\Facades\Route::has('tp.media.index') ? route('tp.media.index') : '',
                                    'editorMode' => $editorMode,
                                    'mode' => $mode,
                                ])
                            @else
                                @if (! $editorMode && $isBuilderDriver && $mode === 'edit')
                                    <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                                        <div class="font-semibold">Visual Builder opens in full-screen mode.</div>
                                        <div class="mt-1">
                                            Use full-screen mode to edit layout and block fields.
                                            <a href="{{ route('tp.pages.editor', ['page' => $page->id]) }}" class="underline decoration-sky-400 underline-offset-2">Open Visual Builder</a>
                                        </div>
                                    </div>
                                @endif
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
                                                    @if (! ($workflowPluginEnabled ?? false) && $page->status === 'draft')
                                                        <form
                                                            method="POST"
                                                            action="{{ route('tp.pages.publish', ['page' => $page->id]) }}">
                                                            @csrf
                                                            <button class="tp-button-primary" type="submit">Publish</button>
                                                        </form>
                                                    @endif
                                
                                                    @if (! ($workflowPluginEnabled ?? false) && $page->status === 'published')
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
                                                        Exit full-screen
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

                                @if (! ($workflowPluginEnabled ?? false) && $mode === 'edit' && $page->status === 'draft')
                                    <form
                                        method="POST"
                                        action="{{ route('tp.pages.publish', ['page' => $page->id]) }}">
                                        @csrf
                                        <button class="tp-button-primary w-full justify-center" type="submit">
                                            Publish
                                        </button>
                                    </form>
                                @endif

                                @if (! ($workflowPluginEnabled ?? false) && $mode === 'edit' && $page->status === 'published')
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
                                        data-confirm="Delete this page? This action cannot be undone.">
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

                    @if ($revisionsEnabled)
                        @include('tentapress-revisions::page-metabox', ['page' => $page, 'mode' => $mode])
                    @endif
                    @if (($workflowPluginEnabled ?? false) && $mode === 'edit')
                        @include('tentapress-workflow::workflow.metabox', ['page' => $page])
                    @endif
                    @if ($taxonomiesPluginEnabled)
                        @includeIf('tentapress-taxonomies::page-metabox', ['page' => $page, 'mode' => $mode])
                    @endif
                    @includeIf('tentapress-seo::page-metabox', ['page' => $page, 'mode' => $mode])
                </div>
            @endif
        </div>
    </div>
@endsection

@once
    @push('scripts')
        <script>
            (() => {
                if (window.tpEditorSwitchInit === true) {
                    return;
                }
                window.tpEditorSwitchInit = true;

                const forms = document.querySelectorAll(
                    'form[data-editor-switch-form="1"]',
                );

                forms.forEach((form) => {
                    const radios = Array.from(
                        form.querySelectorAll(
                            'input[type="radio"][name="editor_driver"][data-editor-switch-radio]',
                        ),
                    );

                    if (radios.length < 2) {
                        return;
                    }

                    let suppressChange = false;
                    let current =
                        form.dataset.editorDriverCurrent ||
                        (radios.find((radio) => radio.checked)?.value ?? '');

                    const chooseRadio = (value) => {
                        const target = radios.find((radio) => radio.value === value);
                        if (target) {
                            target.checked = true;
                        }
                    };

                    radios.forEach((radio) => {
                        radio.addEventListener('change', () => {
                            if (suppressChange || !radio.checked) {
                                return;
                            }

                            const next = String(radio.value || '').trim();
                            if (next === '' || next === current) {
                                current = next || current;
                                form.dataset.editorDriverCurrent = current;
                                return;
                            }

                            const nextLabel = radio.dataset.editorLabel || 'Editor';
                            window.tpConfirm(
                                `Switch to ${nextLabel}? This will save your changes and reload the editor.`,
                                {
                                    title: 'Switch editor?',
                                    confirmText: 'Switch',
                                },
                            ).then((ok) => {
                                if (!ok) {
                                    suppressChange = true;
                                    chooseRadio(current);
                                    suppressChange = false;
                                    return;
                                }

                                current = next;
                                form.dataset.editorDriverCurrent = current;
                                const returnTo = form.querySelector(
                                    'input[name="return_to"][data-editor-return-to]',
                                );
                                if (returnTo instanceof HTMLInputElement) {
                                    returnTo.value = next === 'builder' ? 'editor' : '';
                                }
                                form.requestSubmit();
                            });
                        });
                    });
                });
            })();

            (() => {
                const forms = document.querySelectorAll(
                    'form[data-revisions-autosave-url]',
                );

                forms.forEach((form) => {
                    const autosaveUrl = form.dataset.revisionsAutosaveUrl || '';
                    if (autosaveUrl === '') {
                        return;
                    }

                    const statusNode = document.querySelector(
                        '[data-revisions-autosave-status]',
                    );
                    const csrf =
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '';
                    let dirty = false;
                    let pending = false;

                    const markStatus = (message) => {
                        if (statusNode instanceof HTMLElement) {
                            statusNode.textContent = message;
                        }
                    };

                    const scheduleSave = async () => {
                        if (!dirty || pending) {
                            return;
                        }

                        pending = true;
                        markStatus('Saving autosave...');

                        try {
                            const response = await fetch(autosaveUrl, {
                                method: 'POST',
                                headers: {
                                    Accept: 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                },
                                body: new FormData(form),
                            });

                            if (!response.ok) {
                                throw new Error('Autosave request failed.');
                            }

                            const payload = await response.json();
                            dirty = false;
                            markStatus(
                                payload.saved_at
                                    ? `Autosaved at ${new Date(payload.saved_at).toLocaleTimeString()}.`
                                    : 'No autosave changes detected.',
                            );
                        } catch {
                            markStatus('Autosave failed. Changes remain local until retry.');
                        } finally {
                            pending = false;
                        }
                    };

                    form.addEventListener('input', () => {
                        dirty = true;
                        markStatus('Autosave pending...');
                    });
                    form.addEventListener('change', () => {
                        dirty = true;
                        markStatus('Autosave pending...');
                    });

                    window.setInterval(scheduleSave, 15000);
                });
            })();
        </script>
    @endpush
@endonce
