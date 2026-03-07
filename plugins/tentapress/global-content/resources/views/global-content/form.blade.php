@extends('tentapress-admin::layouts.shell')

@php
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
@endphp

@section('title', $mode === 'create' ? 'Create Global Content' : 'Edit Global Content')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">{{ $mode === 'create' ? 'Create Global Content' : 'Edit Global Content' }}</h1>
            <p class="tp-description">Reusable synced sections and template parts.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('tp.global-content.index') }}" class="tp-button-secondary">Back to library</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <div class="space-y-6 lg:col-span-3">
            <div class="tp-metabox">
                <div class="tp-metabox__body space-y-4">
                    <form method="POST" action="{{ $mode === 'create' ? route('tp.global-content.store') : route('tp.global-content.update', ['globalContent' => $globalContent->id]) }}" id="global-content-form" class="space-y-4">
                        @csrf
                        @if ($mode === 'edit')
                            @method('PUT')
                        @endif

                        @if (count($editorDriverMap) > 1)
                            <div class="tp-field">
                                <label class="tp-label">Editing Experience</label>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    @foreach ($editorDriverMap as $driverId => $driverDefinition)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="editor_driver" value="{{ $driverId }}" class="sr-only peer" @checked($editorDriver === $driverId) />
                                            <div class="rounded-xl border border-slate-200 bg-white p-4 transition peer-checked:border-sky-300 peer-checked:bg-sky-50/60">
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

                        @if (! $usesBlocksEditor && $selectedEditorView)
                            @include($selectedEditorView, [
                                'page' => $globalContent,
                                'blocksJson' => $blocksJson ?? '[]',
                                'blockDefinitions' => $blockDefinitions ?? [],
                                'mediaOptions' => $mediaOptions ?? [],
                                'mediaIndexUrl' => \Illuminate\Support\Facades\Route::has('tp.media.index') ? route('tp.media.index') : '',
                                'resourceOverride' => 'global-content',
                                'storageKeyOverride' => 'tp.builder.global-content.'.((int) ($globalContent->id ?? 0)),
                            ])
                        @else
                            @component('tentapress-blocks::editor', [
                                'blocksJson' => $blocksJson ?? '[]',
                                'blockDefinitions' => $blockDefinitions ?? [],
                                'mediaOptions' => $mediaOptions ?? [],
                                'mediaIndexUrl' => \Illuminate\Support\Facades\Route::has('tp.media.index') ? route('tp.media.index') : '',
                            ])
                            @endcomponent
                        @endif
                    </form>
                </div>
            </div>
        </div>

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
    </div>
@endsection
