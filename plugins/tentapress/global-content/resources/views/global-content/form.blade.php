@extends('tentapress-admin::layouts.shell')

@php
    $editorMode = (bool) ($editorMode ?? false);
    $editorDrivers = is_array($editorDrivers ?? null) ? $editorDrivers : [];
    $editorDriverMap = [];

    foreach ($editorDrivers as $definition) {
        if ($definition instanceof \TentaPress\System\Editor\EditorDriverDefinition) {
            $editorDriverMap[$definition->id] = $definition;
        }
    }

    $editorDriver = old('editor_driver', (string) ($globalContent->editor_driver ?? 'blocks'));
    $editorDriver = is_string($editorDriver) && isset($editorDriverMap[$editorDriver]) ? $editorDriver : 'blocks';
    $selectedDriver = $editorDriverMap[$editorDriver] ?? null;
    $selectedEditorView = $selectedDriver?->viewFor('pages');
    $selectedEditorView = is_string($selectedEditorView) && view()->exists($selectedEditorView) ? $selectedEditorView : null;
    $usesBlocksEditor = $selectedDriver?->usesBlocksEditor ?? true;
    $isBuilderDriver = ($selectedDriver?->id ?? '') === 'builder';
    $canRenderSelectedEditorInline = $editorMode || ! $isBuilderDriver;
@endphp

@if ($editorMode)
    @section('shell_fullscreen', '1')
    @section('body_class', 'bg-slate-100')
@endif

@section('title', $editorMode ? ($selectedDriver?->label ?? 'Visual Builder') : ($mode === 'create' ? 'Create Global Content' : 'Edit Global Content'))

@section('content')
    <div class="{{ $editorMode ? 'space-y-0 px-4 pt-0 pb-6 sm:px-6 lg:px-8' : 'space-y-6' }}">
        @if (! $editorMode)
            <div class="tp-page-header">
                <div>
                    <h1 class="tp-page-title">{{ $mode === 'create' ? 'Create Global Content' : 'Edit Global Content' }}</h1>
                    <p class="tp-description">Reusable synced sections and template parts.</p>
                </div>
                <div class="flex gap-2">
                    @if ($mode === 'edit' && $isBuilderDriver)
                        <a href="{{ route('tp.global-content.editor', ['globalContent' => $globalContent->id]) }}" class="tp-button-secondary">Full-screen editor</a>
                    @endif
                    <a href="{{ route('tp.global-content.index') }}" class="tp-button-secondary">Back to library</a>
                </div>
            </div>
        @endif

        <div class="{{ $editorMode ? 'lg:grid-cols-1' : 'lg:grid-cols-4' }} grid grid-cols-1 gap-6">
            <div class="{{ $editorMode ? 'lg:col-span-1' : 'lg:col-span-3' }} space-y-6">
                <div class="{{ $editorMode ? '' : 'tp-metabox' }}">
                    <div class="{{ $editorMode ? 'space-y-4' : 'tp-metabox__body space-y-4' }}">
                    <form
                        method="POST"
                        action="{{ $mode === 'create' ? route('tp.global-content.store') : route('tp.global-content.update', ['globalContent' => $globalContent->id]) }}"
                        id="global-content-form"
                        class="space-y-4"
                        @if ($mode === 'edit' && count($editorDriverMap) > 1)
                            data-editor-switch-form="1"
                            data-editor-driver-current="{{ $editorDriver }}"
                        @endif>
                        @csrf
                        @if ($mode === 'edit')
                            @method('PUT')
                        @endif

                        @if ($editorMode)
                            <input type="hidden" name="title" value="{{ old('title', $globalContent->title) }}" />
                            <input type="hidden" name="slug" value="{{ old('slug', $globalContent->slug) }}" />
                            <input type="hidden" name="kind" value="{{ old('kind', $globalContent->kind) }}" />
                            <input type="hidden" name="status" value="{{ old('status', $globalContent->status) }}" />
                            <input type="hidden" name="description" value="{{ old('description', $globalContent->description) }}" />
                            <input type="hidden" name="editor_driver" value="{{ $editorDriver }}" />
                            <input type="hidden" name="return_to" value="editor" />
                            <input type="hidden" name="editor_mode" value="1" />
                        @elseif (count($editorDriverMap) > 1)
                            <div class="tp-field">
                                <label class="tp-label">Editing Experience</label>
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
                                            <div
                                                @class([
                                                    'rounded-xl border bg-white p-4 transition',
                                                    'border-slate-900 ring-2 ring-slate-200' => $editorDriver === $driverId,
                                                    'border-slate-200' => $editorDriver !== $driverId,
                                                    'peer-checked:border-slate-900 peer-checked:ring-2 peer-checked:ring-slate-200',
                                                ])>
                                                <div class="font-semibold text-slate-900">{{ $driverDefinition->label }}</div>
                                                <div class="mt-1 text-xs text-slate-500">{{ $driverDefinition->description }}</div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                <div class="tp-help">Choose the authoring experience for this reusable content.</div>
                            </div>
                        @else
                            <input type="hidden" name="editor_driver" value="{{ $editorDriver }}" />
                        @endif

                        @if (! $usesBlocksEditor && $selectedEditorView && $canRenderSelectedEditorInline)
                            @include($selectedEditorView, [
                                'globalContent' => $globalContent,
                                'page' => $globalContent,
                                'editorTitle' => trim((string) ($globalContent->title ?? '')) !== '' ? $globalContent->title : 'Untitled Global Content',
                                'blocksJson' => $blocksJson ?? '[]',
                                'blockDefinitions' => $blockDefinitions ?? [],
                                'mediaOptions' => $mediaOptions ?? [],
                                'mediaIndexUrl' => \Illuminate\Support\Facades\Route::has('tp.media.index') ? route('tp.media.index') : '',
                                'editorMode' => $editorMode,
                                'mode' => $mode,
                                'resourceOverride' => 'global-content',
                                'storageKeyOverride' => 'tp.builder.global-content.'.((int) ($globalContent->id ?? 0)),
                            ])
                        @else
                            @if (! $editorMode && $isBuilderDriver)
                                <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                                    <div class="font-semibold">Visual Builder opens in full-screen mode.</div>
                                    <div class="mt-1">
                                        @if ($mode === 'edit')
                                            Use full-screen mode to edit layout and block fields.
                                            <a href="{{ route('tp.global-content.editor', ['globalContent' => $globalContent->id]) }}" class="underline decoration-sky-400 underline-offset-2">Open Visual Builder</a>
                                        @else
                                            Save this entry to continue in the full-screen Visual Builder.
                                        @endif
                                    </div>
                                </div>
                                <textarea name="blocks_json" class="hidden">{{ $blocksJson ?? '[]' }}</textarea>
                            @else
                            @component('tentapress-blocks::editor', [
                                'blocksJson' => $blocksJson ?? '[]',
                                'blockDefinitions' => $blockDefinitions ?? [],
                                'mediaOptions' => $mediaOptions ?? [],
                                'mediaIndexUrl' => \Illuminate\Support\Facades\Route::has('tp.media.index') ? route('tp.media.index') : '',
                            ])
                            @endcomponent
                            @endif
                        @endif
                    </form>
                </div>
            </div>
        </div>

        @if (! $editorMode)
            <div class="space-y-6 lg:sticky lg:top-6 lg:self-start">
                <div class="tp-metabox">
                    <div class="tp-metabox__title">Details</div>
                    <div class="tp-metabox__body space-y-4">
                    <div class="tp-field">
                        <label class="tp-label">Title</label>
                        <input form="global-content-form" type="text" name="title" class="tp-input" value="{{ old('title', $globalContent->title) }}" required />
                    </div>

                    <div class="tp-field">
                        <label class="tp-label">Slug</label>
                        <input form="global-content-form" type="text" name="slug" class="tp-input" value="{{ old('slug', $globalContent->slug) }}" placeholder="auto-generated if blank" />
                        <div class="tp-help">Lowercase letters, numbers, and dashes only.</div>
                    </div>

                    <div class="tp-field">
                        <label class="tp-label">Kind</label>
                        <select form="global-content-form" name="kind" class="tp-select">
                            <option value="section" @selected(old('kind', $globalContent->kind) === 'section')>Section</option>
                            <option value="template_part" @selected(old('kind', $globalContent->kind) === 'template_part')>Template Part</option>
                        </select>
                    </div>

                    <div class="tp-field">
                        <label class="tp-label">Status</label>
                        <select form="global-content-form" name="status" class="tp-select">
                            <option value="draft" @selected(old('status', $globalContent->status) === 'draft')>Draft</option>
                            <option value="published" @selected(old('status', $globalContent->status) === 'published')>Published</option>
                        </select>
                    </div>

                    <div class="tp-field">
                        <label class="tp-label">Description</label>
                        <textarea form="global-content-form" name="description" class="tp-textarea" rows="4" placeholder="Optional internal notes.">{{ old('description', $globalContent->description) }}</textarea>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        <div class="font-semibold text-slate-900">Usage</div>
                        <div class="mt-1">
                            {{ $mode === 'edit' ? (($globalContent->usages()->count() > 0 ? $globalContent->usages()->count().' recorded page/post reference'.($globalContent->usages()->count() === 1 ? '' : 's') : 'No recorded page or post references yet.')) : 'Usage appears after this entry is referenced by a page or post.' }}
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button form="global-content-form" type="submit" class="tp-button-primary">{{ $mode === 'create' ? 'Create global content' : 'Save changes' }}</button>
                        @if ($mode === 'edit')
                            <form method="POST" action="{{ route('tp.global-content.destroy', ['globalContent' => $globalContent->id]) }}" data-confirm="Delete this global content entry? This cannot be undone.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="tp-button-secondary text-red-600 hover:text-red-700">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            </div>
        @endif
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
                                form.requestSubmit();
                            });
                        });
                    });
                });
            })();
        </script>
    @endpush
@endonce
