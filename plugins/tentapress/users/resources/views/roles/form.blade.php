@extends('tentapress-admin::layouts.shell')

@section('title', $mode === 'create' ? 'Create Role' : 'Edit Role')

@section('content')
    <div class="tp-editor space-y-6">
        <div class="tp-page-header">
            <div>
                <h1 class="tp-page-title">{{ $mode === 'create' ? 'Create Role' : 'Edit Role' }}</h1>
                <p class="tp-description">Choose what this role is allowed to do.</p>
            </div>
        </div>

        @php
            $selected = is_array($selected ?? null) ? $selected : [];
        @endphp

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            <div class="space-y-6 lg:col-span-3">
                <div class="tp-metabox">
                    <div class="tp-metabox__body">
                        <form
                            method="POST"
                            action="{{ $mode === 'create' ? route('tp.roles.store') : route('tp.roles.update', ['role' => $role->id]) }}"
                            class="space-y-5"
                            id="role-form">
                            @csrf
                            @if ($mode === 'edit')
                                @method('PUT')
                            @endif

                            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                                <div class="tp-field">
                                    <label class="tp-label">Name</label>
                                    <input name="name" class="tp-input" value="{{ old('name', $role?->name) }}" required />
                                </div>

                                <div class="tp-field">
                                    <label class="tp-label">Role key</label>
                                    <input
                                        name="slug"
                                        class="tp-input"
                                        value="{{ old('slug', $role?->slug) }}"
                                        placeholder="administrator"
                                        required />
                                    <div class="tp-help">Lowercase, numbers and dashes only.</div>
                                </div>
                            </div>

                            <div class="tp-divider"></div>

                            <div>
                                <div class="tp-label mb-2">Capabilities</div>

                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                    @foreach ($capabilities as $cap)
                                        @php
                                            $key = (string) $cap->key;
                                            $label = (string) $cap->label;
                                            $group = (string) ($cap->group ?? '');
                                            $desc = (string) ($cap->description ?? '');
                                            $isChecked =
                                                in_array($key, $selected, true) ||
                                                in_array($key, (array) old('capabilities', []), true);
                                        @endphp

                                        <label class="tp-panel flex cursor-pointer items-start gap-3">
                                            <input
                                                type="checkbox"
                                                class="tp-checkbox mt-1"
                                                name="capabilities[]"
                                                value="{{ $key }}"
                                                @checked($isChecked) />
                                            <span class="block">
                                                <span class="block text-sm font-semibold">{{ $label }}</span>
                                                <span class="tp-muted mt-1 block text-xs">
                                                    <code class="tp-code">{{ $key }}</code>
                                                    @if ($group !== '')
                                                        <span class="mx-1">·</span>
                                                        {{ $group }}
                                                    @endif
                                                </span>
                                                @if ($desc !== '')
                                                    <span class="tp-muted mt-1 block text-xs">{{ $desc }}</span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="space-y-6 lg:sticky lg:top-6 lg:self-start">
                <div class="tp-metabox">
                    <div class="tp-metabox__title">Actions</div>
                    <div class="tp-metabox__body space-y-2 text-sm">
                        <button type="submit" form="role-form" class="tp-button-primary w-full justify-center">
                            {{ $mode === 'create' ? 'Create Role' : 'Save Changes' }}
                        </button>
                        <a href="{{ route('tp.roles.index') }}" class="tp-button-secondary w-full justify-center">
                            Back to roles
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
