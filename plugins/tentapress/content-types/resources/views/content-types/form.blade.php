@extends('tentapress-admin::layouts.shell')

@section('title', $mode === 'edit' ? 'Edit Content Type' : 'Create Content Type')

@section('content')
    @php
        $storedFields = old('fields_json');
        $decodedStoredFields = is_string($storedFields) ? json_decode($storedFields, true) : null;
        $initialFields = is_array($decodedStoredFields)
            ? $decodedStoredFields
            : $contentType->fields
                ->map(
                    fn ($field): array => [
                        'key' => (string) $field->key,
                        'label' => (string) $field->label,
                        'field_type' => (string) $field->field_type,
                        'required' => (bool) $field->required,
                        'config' => is_array($field->config) ? $field->config : [],
                    ],
                )
                ->values()
                ->all();
    @endphp

    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">{{ $mode === 'edit' ? 'Edit Content Type' : 'Create Content Type' }}</h1>
            <p class="tp-description">Define the schema, public base path, and delivery rules for this content type.</p>
        </div>

        @if ($mode === 'edit')
            <div class="flex gap-2">
                <a href="{{ route('tp.content-types.entries.index', ['contentType' => $contentType->id]) }}" class="tp-button-secondary">Manage entries</a>
            </div>
        @endif
    </div>

    <form
        method="POST"
        action="{{ $mode === 'edit' ? route('tp.content-types.update', ['contentType' => $contentType->id]) : route('tp.content-types.store') }}"
        class="space-y-6"
        x-data="tpContentTypeSchemaBuilder(@js($initialFields))"
        x-init="sync()"
        x-effect="sync()">
        @csrf
        @if ($mode === 'edit')
            @method('PUT')
        @endif

        <input x-ref="fieldsJson" type="hidden" name="fields_json" value="{{ is_string($storedFields) ? $storedFields : '[]' }}" />

        <div class="tp-metabox">
            <div class="tp-metabox__title">Details</div>
            <div class="tp-metabox__body space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <label class="block">
                        <span class="tp-label">Key</span>
                        <input type="text" name="key" value="{{ old('key', $contentType->key) }}" class="tp-input mt-2 w-full" placeholder="case-studies" />
                        <span class="mt-2 block text-xs text-black/60">Used internally and in API payloads. Lowercase kebab-case only.</span>
                        @error('key')
                            <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block">
                        <span class="tp-label">Base path</span>
                        <input type="text" name="base_path" value="{{ old('base_path', $contentType->base_path) }}" class="tp-input mt-2 w-full" placeholder="case-studies" />
                        <span class="mt-2 block text-xs text-black/60">Public archive and entry routes live under this path.</span>
                        @error('base_path')
                            <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block">
                        <span class="tp-label">Singular label</span>
                        <input type="text" name="singular_label" value="{{ old('singular_label', $contentType->singular_label) }}" class="tp-input mt-2 w-full" placeholder="Case Study" />
                        @error('singular_label')
                            <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block">
                        <span class="tp-label">Plural label</span>
                        <input type="text" name="plural_label" value="{{ old('plural_label', $contentType->plural_label) }}" class="tp-input mt-2 w-full" placeholder="Case Studies" />
                        @error('plural_label')
                            <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block md:col-span-2">
                        <span class="tp-label">Description</span>
                        <textarea name="description" rows="3" class="tp-textarea mt-2 w-full" placeholder="Explain when this content type should be used.">{{ old('description', $contentType->description) }}</textarea>
                        @error('description')
                            <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block">
                        <span class="tp-label">Default layout</span>
                        <input type="text" name="default_layout" value="{{ old('default_layout', $contentType->default_layout) }}" class="tp-input mt-2 w-full" placeholder="default" />
                    </label>

                    <label class="block">
                        <span class="tp-label">Default editor driver</span>
                        <input type="text" name="default_editor_driver" value="{{ old('default_editor_driver', $contentType->default_editor_driver ?: 'blocks') }}" class="tp-input mt-2 w-full" placeholder="blocks" />
                        <span class="mt-2 block text-xs text-black/60">`blocks` is the safe default. Other drivers are optional compatibility.</span>
                        @error('default_editor_driver')
                            <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block">
                        <span class="tp-label">API visibility</span>
                        <select name="api_visibility" class="tp-select mt-2 w-full">
                            <option value="disabled" @selected(old('api_visibility', $contentType->api_visibility ?: 'disabled') === 'disabled')>Disabled</option>
                            <option value="public" @selected(old('api_visibility', $contentType->api_visibility) === 'public')>Public</option>
                        </select>
                        @error('api_visibility')
                            <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="block">
                        <span class="tp-label">Archive</span>
                        <label class="mt-3 inline-flex items-center gap-3 text-sm text-black/80">
                            <input type="hidden" name="archive_enabled" value="0" />
                            <input type="checkbox" name="archive_enabled" value="1" class="tp-checkbox" @checked((bool) old('archive_enabled', $contentType->archive_enabled ?? true)) />
                            <span>Enable public archive route</span>
                        </label>
                    </label>
                </div>
            </div>
        </div>

        <div class="tp-metabox">
            <div class="tp-metabox__title flex items-center justify-between gap-4">
                <div>
                    <div class="font-medium text-black">Fields</div>
                    <div class="mt-1 text-sm text-black/60">Define structured fields stored alongside the built-in title, slug, and body.</div>
                </div>
                <button type="button" class="tp-button-secondary" @click="addField()">Add field</button>
            </div>

            <div class="tp-metabox__body space-y-4">
                @error('fields_json')
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
                @enderror

                <template x-if="fields.length === 0">
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-black/60">
                        No custom fields yet. Add fields for structured values such as dates, selects, and relationships.
                    </div>
                </template>

                <template x-for="(field, index) in fields" :key="field.local_id">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="grid gap-4 md:grid-cols-4">
                            <label class="block">
                                <span class="tp-label">Field key</span>
                                <input x-model="field.key" type="text" class="tp-input mt-2 w-full" placeholder="event_date" />
                            </label>

                            <label class="block">
                                <span class="tp-label">Label</span>
                                <input x-model="field.label" type="text" class="tp-input mt-2 w-full" placeholder="Event date" />
                            </label>

                            <label class="block">
                                <span class="tp-label">Type</span>
                                <select x-model="field.field_type" class="tp-select mt-2 w-full">
                                    <option value="text">Text</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="number">Number</option>
                                    <option value="boolean">Boolean</option>
                                    <option value="date_time">Date and time</option>
                                    <option value="select">Select</option>
                                    <option value="relation">Relation</option>
                                </select>
                            </label>

                            <div class="flex items-end justify-between gap-4">
                                <label class="inline-flex items-center gap-3 text-sm text-black/80">
                                    <input x-model="field.required" type="checkbox" class="tp-checkbox" />
                                    <span>Required</span>
                                </label>

                                <button type="button" class="tp-button-link text-red-600 hover:text-red-700" @click="removeField(index)">Remove</button>
                            </div>
                        </div>

                        <div class="mt-4" x-show="field.field_type === 'select'">
                            <label class="block">
                                <span class="tp-label">Options</span>
                                <textarea
                                    x-model="field.select_options"
                                    rows="4"
                                    class="tp-textarea mt-2 w-full"
                                    placeholder="planned | Planned&#10;live | Live"></textarea>
                                <span class="mt-2 block text-xs text-black/60">One option per line using `value | label`.</span>
                            </label>
                        </div>

                        <div class="mt-4" x-show="field.field_type === 'relation'">
                            <label class="block">
                                <span class="tp-label">Allowed sources</span>
                                <input
                                    x-model="field.allowed_sources"
                                    type="text"
                                    class="tp-input mt-2 w-full"
                                    placeholder="content-types, pages, posts" />
                                <span class="mt-2 block text-xs text-black/60">Comma-separated source keys. Leave the default to keep relations scoped to content type entries.</span>
                            </label>

                            <label class="block">
                                <span class="tp-label">Allowed content type keys</span>
                                <input
                                    x-model="field.allowed_type_keys"
                                    type="text"
                                    class="tp-input mt-2 w-full"
                                    placeholder="{{ implode(', ', $existingTypeKeys) }}" />
                                <span class="mt-2 block text-xs text-black/60">Only applied to the `content-types` source. Leave blank to allow any content type entry from that source.</span>
                            </label>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('tp.content-types.index') }}" class="tp-button-secondary">Cancel</a>
            <button type="submit" class="tp-button-primary">{{ $mode === 'edit' ? 'Save changes' : 'Create content type' }}</button>
        </div>
    </form>

    <script>
        function tpContentTypeSchemaBuilder(initialFields) {
            const rows = Array.isArray(initialFields) ? initialFields : [];

            return {
                fields: rows.map((field, index) => ({
                    local_id: `${Date.now()}-${index}`,
                    key: field.key ?? '',
                    label: field.label ?? '',
                    field_type: field.field_type ?? 'text',
                    required: Boolean(field.required ?? false),
                    select_options: Array.isArray(field.config?.options)
                        ? field.config.options.map((option) => `${option.value ?? ''} | ${option.label ?? ''}`.trim()).join('\n')
                        : '',
                    allowed_type_keys: Array.isArray(field.config?.allowed_type_keys)
                        ? field.config.allowed_type_keys.join(', ')
                        : '',
                    allowed_sources: Array.isArray(field.config?.allowed_sources)
                        ? field.config.allowed_sources.join(', ')
                        : 'content-types',
                })),
                addField() {
                    this.fields.push({
                        local_id: `${Date.now()}-${Math.random()}`,
                        key: '',
                        label: '',
                        field_type: 'text',
                        required: false,
                        select_options: '',
                        allowed_sources: 'content-types',
                        allowed_type_keys: '',
                    });
                },
                removeField(index) {
                    this.fields.splice(index, 1);
                },
                sync() {
                    this.$refs.fieldsJson.value = JSON.stringify(
                        this.fields.map((field) => ({
                            key: String(field.key || '').trim(),
                            label: String(field.label || '').trim(),
                            field_type: String(field.field_type || 'text').trim(),
                            required: Boolean(field.required),
                            config: this.configFor(field),
                        })),
                    );
                },
                configFor(field) {
                    if (field.field_type === 'select') {
                        return {
                            options: String(field.select_options || '')
                                .split(/\r?\n/)
                                .map((line) => line.trim())
                                .filter(Boolean)
                                .map((line) => {
                                    const [value, label] = line.split('|').map((part) => String(part || '').trim());
                                    return {
                                        value: value || label,
                                        label: label || value,
                                    };
                                }),
                        };
                    }

                    if (field.field_type === 'relation') {
                        return {
                            allowed_sources: String(field.allowed_sources || '')
                                .split(',')
                                .map((value) => value.trim())
                                .filter(Boolean),
                            allowed_type_keys: String(field.allowed_type_keys || '')
                                .split(',')
                                .map((value) => value.trim())
                                .filter(Boolean),
                        };
                    }

                    return {};
                },
            };
        }
    </script>
@endsection
