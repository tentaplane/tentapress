@extends('tentapress-admin::layouts.shell')

@section('title', 'Content Types')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Content Types</h1>
            <p class="tp-description">Create and manage structured content models for your site.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.content-types.create') }}" class="tp-button-primary">Create content type</a>
        </div>
    </div>

    <div class="tp-metabox">
        @if ($contentTypes->count() === 0)
            <div class="tp-metabox__body tp-muted text-sm">No content types have been defined yet.</div>
        @else
            <div class="tp-table-wrap">
                <table class="tp-table tp-table--responsive">
                    <thead class="tp-table__thead">
                        <tr>
                            <th class="tp-table__th">Content type</th>
                            <th class="tp-table__th">Base path</th>
                            <th class="tp-table__th">Fields</th>
                            <th class="tp-table__th">Entries</th>
                            <th class="tp-table__th">API</th>
                            <th class="tp-table__th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="tp-table__tbody">
                        @foreach ($contentTypes as $contentType)
                            <tr class="tp-table__row">
                                <td data-label="Content type" class="tp-table__td align-middle py-4">
                                    <div class="font-medium text-black">{{ $contentType->plural_label }}</div>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-black/60">
                                        <span>{{ $contentType->singular_label }}</span>
                                        <code class="tp-code">{{ $contentType->key }}</code>
                                    </div>
                                    @if ($contentType->description)
                                        <div class="mt-1 text-sm text-black/60">{{ $contentType->description }}</div>
                                    @endif
                                </td>
                                <td data-label="Base path" class="tp-table__td align-middle py-4">
                                    <code class="tp-code">/{{ $contentType->base_path }}</code>
                                </td>
                                <td data-label="Fields" class="tp-table__td align-middle py-4">{{ (int) $contentType->fields_count }}</td>
                                <td data-label="Entries" class="tp-table__td align-middle py-4">{{ (int) $contentType->entries_count }}</td>
                                <td data-label="API" class="tp-table__td align-middle py-4">
                                    @if ($contentType->api_visibility === 'public')
                                        <span class="tp-badge tp-badge-success">Public</span>
                                    @else
                                        <span class="tp-badge tp-badge-info">Disabled</span>
                                    @endif
                                </td>
                                <td data-label="Actions" class="tp-table__td align-middle py-4">
                                    <div class="tp-muted flex justify-end gap-3 text-xs">
                                        <a class="tp-button-link" href="{{ route('tp.content-types.entries.index', ['contentType' => $contentType->id]) }}">Entries</a>
                                        <a class="tp-button-link" href="{{ route('tp.content-types.edit', ['contentType' => $contentType->id]) }}">Edit</a>
                                        <form
                                            method="POST"
                                            action="{{ route('tp.content-types.destroy', ['contentType' => $contentType->id]) }}"
                                            data-confirm="Delete this content type and all of its entries? This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="tp-button-link text-red-600 hover:text-red-700">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="tp-metabox__body">{{ $contentTypes->links() }}</div>
        @endif
    </div>
@endsection
