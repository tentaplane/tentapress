@extends('tentapress-admin::layouts.shell')

@section('title', $mode === 'edit' ? 'Edit '.$contentType->singular_label : 'Create '.$contentType->singular_label)

@section('content')
    @php
        $driverMap = [];
        foreach ($driverDefinitions as $definition) {
            if (! $definition instanceof \TentaPress\System\Editor\EditorDriverDefinition) {
                continue;
            }

            $driverMap[$definition->id] = $definition;
        }

        $formTitle = old('title', (string) $entry->title);
        $formSlug = old('slug', (string) $entry->slug);
        $formLayout = old('layout', (string) ($entry->layout ?: $contentType->default_layout));
        $formEditorDriver = old('editor_driver', (string) ($entry->editor_driver ?: $contentType->default_editor_driver ?: 'blocks'));
        if (! isset($driverMap[$formEditorDriver])) {
            $formEditorDriver = array_key_first($driverMap) ?? 'blocks';
        }

        $selectedDriver = $driverMap[$formEditorDriver] ?? null;
        $usesBlocksEditor = $selectedDriver?->usesBlocksEditor ?? true;
        $selectedEditorView = $selectedDriver?->viewFor('content-types');
        $selectedEditorView = is_string($selectedEditorView) && view()->exists($selectedEditorView) ? $selectedEditorView : null;

        $blocksJson = old('blocks_json');
        if (! is_string($blocksJson)) {
            $blocksJson = json_encode(is_array($entry->blocks) ? $entry->blocks : [], JSON_THROW_ON_ERROR);
        }

        $pageDocJson = old('page_doc_json');
        if (! is_string($pageDocJson)) {
            $pageDocJson = json_encode(is_array($entry->content) ? $entry->content : null, JSON_THROW_ON_ERROR);
        }

        $fieldValues = old('field_values');
        if (! is_array($fieldValues)) {
            $fieldValues = is_array($entry->field_values) ? $entry->field_values : [];
        }
    @endphp

    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">{{ $mode === 'edit' ? 'Edit '.$contentType->singular_label : 'Create '.$contentType->singular_label }}</h1>
            <p class="tp-description">Entries live under <code class="tp-code">/{{ $contentType->base_path }}</code> and follow the schema defined by this content type.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.content-types.entries.index', ['contentType' => $contentType->id]) }}" class="tp-button-secondary">Back to entries</a>
        </div>
    </div>

    <form
        method="POST"
        action="{{ $mode === 'edit' ? route('tp.content-types.entries.update', ['contentType' => $contentType->id, 'entry' => $entry->id]) : route('tp.content-types.entries.store', ['contentType' => $contentType->id]) }}"
        class="space-y-6">
        @csrf
        @if ($mode === 'edit')
            @method('PUT')
        @endif

        <div class="tp-metabox">
            <div class="tp-metabox__title">Entry details</div>
            <div class="tp-metabox__body space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <label class="block">
                        <span class="tp-label">Title</span>
                        <input type="text" name="title" value="{{ $formTitle }}" class="tp-input mt-2 w-full" />
                        @error('title')
                            <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block">
                        <span class="tp-label">Slug</span>
                        <input type="text" name="slug" value="{{ $formSlug }}" class="tp-input mt-2 w-full" />
                        @error('slug')
                            <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block">
                        <span class="tp-label">Layout</span>
                        <input type="text" name="layout" value="{{ $formLayout }}" class="tp-input mt-2 w-full" placeholder="default" />
                    </label>

                    <label class="block">
                        <span class="tp-label">Editor driver</span>
                        <select name="editor_driver" class="tp-select mt-2 w-full">
                            @foreach ($driverMap as $driverId => $definition)
                                <option value="{{ $driverId }}" @selected($formEditorDriver === $driverId)>{{ $definition->label }}</option>
                            @endforeach
                        </select>
                        @error('editor_driver')
                            <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </label>
                </div>
            </div>
        </div>

        @if ($contentType->fields->count() > 0)
            <div class="tp-metabox">
                <div class="tp-metabox__title">Structured fields</div>
                <div class="tp-metabox__body grid gap-6 md:grid-cols-2">
                    @foreach ($contentType->fields as $field)
                        @php
                            $fieldValue = $fieldValues[$field->key] ?? null;
                            $fieldName = 'field_values['.$field->key.']';
                            $selectedRelationValue = is_numeric($fieldValue) ? 'content-types:'.$fieldValue : (string) $fieldValue;
                        @endphp

                        <label class="block {{ $field->field_type === 'textarea' ? 'md:col-span-2' : '' }}">
                            <span class="tp-label">
                                {{ $field->label }}
                                @if ($field->required)
                                    <span class="text-red-600">*</span>
                                @endif
                            </span>

                            @if ($field->field_type === 'textarea')
                                <textarea name="{{ $fieldName }}" rows="4" class="tp-textarea mt-2 w-full">{{ is_string($fieldValue) ? $fieldValue : '' }}</textarea>
                            @elseif ($field->field_type === 'number')
                                <input type="number" name="{{ $fieldName }}" value="{{ is_numeric($fieldValue) ? $fieldValue : '' }}" class="tp-input mt-2 w-full" />
                            @elseif ($field->field_type === 'boolean')
                                <label class="mt-3 inline-flex items-center gap-3 text-sm text-black/80">
                                    <input type="hidden" name="{{ $fieldName }}" value="0" />
                                    <input type="checkbox" name="{{ $fieldName }}" value="1" class="tp-checkbox" @checked(filter_var($fieldValue, FILTER_VALIDATE_BOOL)) />
                                    <span>Enabled</span>
                                </label>
                            @elseif ($field->field_type === 'date_time')
                                <input
                                    type="datetime-local"
                                    name="{{ $fieldName }}"
                                    value="{{ is_string($fieldValue) && $fieldValue !== '' ? \Carbon\Carbon::parse($fieldValue)->format('Y-m-d\TH:i') : '' }}"
                                    class="tp-input mt-2 w-full" />
                            @elseif ($field->field_type === 'select')
                                <select name="{{ $fieldName }}" class="tp-select mt-2 w-full">
                                    <option value="">Select an option</option>
                                    @foreach ($field->config['options'] ?? [] as $option)
                                        @if (is_array($option))
                                            <option value="{{ $option['value'] ?? '' }}" @selected((string) $fieldValue === (string) ($option['value'] ?? ''))>
                                                {{ $option['label'] ?? $option['value'] ?? '' }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            @elseif ($field->field_type === 'relation')
                                <select name="{{ $fieldName }}" class="tp-select mt-2 w-full">
                                    <option value="">Select an item</option>
                                    @foreach ($relationOptions[$field->key] ?? [] as $option)
                                        <option value="{{ $option['id'] }}" @selected($selectedRelationValue === (string) $option['id'])>
                                            {{ $option['title'] }}@if (($option['type_label'] ?? '') !== '') - {{ $option['type_label'] }}@endif
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" name="{{ $fieldName }}" value="{{ is_string($fieldValue) ? $fieldValue : '' }}" class="tp-input mt-2 w-full" />
                            @endif
                        </label>
                    @endforeach
                </div>

                @error('field_values')
                    <div class="tp-metabox__body pt-0 text-sm text-red-600">{{ $message }}</div>
                @enderror
            </div>
        @endif

        <div class="tp-metabox">
            <div class="tp-metabox__title">Body</div>
            <div class="tp-metabox__body space-y-4">
                @if (! $usesBlocksEditor && $selectedEditorView)
                    @include($selectedEditorView, [
                        'entry' => $entry,
                        'contentType' => $contentType,
                        'pageDocJson' => $pageDocJson,
                        'blocksJson' => $blocksJson,
                        'blockDefinitions' => $blockDefinitions ?? [],
                        'mediaOptions' => $mediaOptions ?? [],
                    ])
                @else
                    @component('tentapress-blocks::editor', [
                        'blocksEditorMode' => true,
                        'editorTitle' => trim($formTitle) !== '' ? $formTitle : 'Untitled '.$contentType->singular_label,
                        'blocksJson' => $blocksJson,
                        'blockDefinitions' => $blockDefinitions ?? [],
                        'mediaOptions' => $mediaOptions ?? [],
                        'mediaIndexUrl' => \Illuminate\Support\Facades\Route::has('tp.media.index') ? route('tp.media.index') : '',
                    ])
                    @endcomponent
                @endif

                <input type="hidden" name="page_doc_json" value="{{ $pageDocJson }}" />
            </div>
        </div>

        @if ($mode === 'edit')
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex gap-2">
                    @if ($entry->status === 'published')
                        <form method="POST" action="{{ route('tp.content-types.entries.unpublish', ['contentType' => $contentType->id, 'entry' => $entry->id]) }}">
                            @csrf
                            <button type="submit" class="tp-button-secondary">Move to draft</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('tp.content-types.entries.publish', ['contentType' => $contentType->id, 'entry' => $entry->id]) }}">
                            @csrf
                            <button type="submit" class="tp-button-secondary">Publish</button>
                        </form>
                    @endif
                </div>

                <div class="flex gap-3">
                    <a href="{{ route('tp.content-types.entries.index', ['contentType' => $contentType->id]) }}" class="tp-button-secondary">Cancel</a>
                    <button type="submit" class="tp-button-primary">Save changes</button>
                </div>
            </div>
        @else
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('tp.content-types.entries.index', ['contentType' => $contentType->id]) }}" class="tp-button-secondary">Cancel</a>
                <button type="submit" class="tp-button-primary">Create entry</button>
            </div>
        @endif
    </form>
@endsection
