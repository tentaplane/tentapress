@extends('tentapress-admin::layouts.shell')

@php
    $isEdit = $mode === 'edit';
    $title = $isEdit ? 'Edit term' : 'Create term';
    $description = $taxonomy->is_hierarchical
        ? 'Create and organise hierarchical terms for this taxonomy.'
        : 'Create and manage flat terms for this taxonomy.';
@endphp

@section('title', $title)

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">{{ $title }}</h1>
            <p class="tp-description">{{ $description }}</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.taxonomies.terms.index', ['taxonomy' => $taxonomy->id]) }}" class="tp-button-secondary">Back to terms</a>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__body">
            <form
                method="POST"
                action="{{ $isEdit ? route('tp.taxonomies.terms.update', ['taxonomy' => $taxonomy->id, 'term' => $term->id]) : route('tp.taxonomies.terms.store', ['taxonomy' => $taxonomy->id]) }}"
                class="space-y-5">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="tp-field">
                    <label class="tp-label">Taxonomy</label>
                    <input class="tp-input" value="{{ $taxonomy->label }}" disabled />
                    <div class="tp-help">
                        <code class="tp-code">{{ $taxonomy->key }}</code>
                    </div>
                </div>

                <div class="tp-field">
                    <label class="tp-label">Name</label>
                    <input
                        name="name"
                        class="tp-input"
                        value="{{ old('name', $term?->name) }}"
                        required />
                    @error('name')
                        <div class="tp-help text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="tp-field">
                    <label class="tp-label">Slug</label>
                    <input
                        name="slug"
                        class="tp-input"
                        value="{{ old('slug', $term?->slug) }}"
                        placeholder="auto-generated if blank"
                        pattern="[a-z0-9-]+" />
                    <div class="tp-help">Lowercase, numbers, and dashes only.</div>
                    @error('slug')
                        <div class="tp-help text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                @if ($taxonomy->is_hierarchical)
                    <div class="tp-field">
                        <label class="tp-label">Parent term</label>
                        <select name="parent_id" class="tp-select">
                            <option value="">No parent</option>
                            @foreach ($parentOptions as $option)
                                <option value="{{ $option->id }}" @selected((string) $option->id === (string) old('parent_id', $term?->parent_id))>
                                    {{ $option->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="tp-help text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div class="tp-field">
                    <label class="tp-label">Description</label>
                    <textarea name="description" class="tp-textarea" rows="5">{{ old('description', $term?->description) }}</textarea>
                    @error('description')
                        <div class="tp-help text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="tp-button-primary">{{ $isEdit ? 'Save changes' : 'Create term' }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
