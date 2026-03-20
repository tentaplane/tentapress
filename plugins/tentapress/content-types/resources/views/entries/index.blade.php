@extends('tentapress-admin::layouts.shell')

@section('title', $contentType->plural_label)

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">{{ $contentType->plural_label }}</h1>
            <p class="tp-description">Manage {{ strtolower($contentType->plural_label) }} for the <code class="tp-code">{{ $contentType->key }}</code> content type.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.content-types.edit', ['contentType' => $contentType->id]) }}" class="tp-button-secondary">Edit type</a>
            <a href="{{ route('tp.content-types.entries.create', ['contentType' => $contentType->id]) }}" class="tp-button-primary">Create entry</a>
        </div>
    </div>

    <div class="tp-metabox">
        @if ($entries->count() === 0)
            <div class="tp-metabox__body tp-muted text-sm">No entries exist for this content type yet.</div>
        @else
            <div class="tp-table-wrap">
                <table class="tp-table tp-table--responsive">
                    <thead class="tp-table__thead">
                        <tr>
                            <th class="tp-table__th">Title</th>
                            <th class="tp-table__th">Status</th>
                            <th class="tp-table__th">Published</th>
                            <th class="tp-table__th">Updated</th>
                            <th class="tp-table__th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="tp-table__tbody">
                        @foreach ($entries as $entry)
                            <tr class="tp-table__row">
                                <td data-label="Title" class="tp-table__td align-middle py-4">
                                    <div class="flex items-center gap-4 whitespace-nowrap">
                                        <a class="tp-button-link" href="{{ route('tp.content-types.entries.edit', ['contentType' => $contentType->id, 'entry' => $entry->id]) }}">
                                            {{ $entry->title }}
                                        </a>
                                        <span class="tp-code">/{{ $contentType->base_path }}/{{ $entry->slug }}</span>
                                    </div>
                                </td>
                                <td data-label="Status" class="tp-table__td align-middle py-4">
                                    @if ($entry->status === 'published')
                                        <span class="tp-badge tp-badge-success">Published</span>
                                    @else
                                        <span class="tp-badge tp-badge-info">Draft</span>
                                    @endif
                                </td>
                                <td data-label="Published" class="tp-table__td align-middle py-4 tp-muted">
                                    {{ $entry->published_at?->format('Y-m-d H:i') ?? '—' }}
                                </td>
                                <td data-label="Updated" class="tp-table__td align-middle py-4 tp-muted">
                                    {{ $entry->updated_at?->diffForHumans() ?? '—' }}
                                </td>
                                <td data-label="Actions" class="tp-table__td align-middle py-4">
                                    <div class="tp-muted flex justify-end gap-3 text-xs">
                                        <a class="tp-button-link" href="{{ route('tp.content-types.entries.edit', ['contentType' => $contentType->id, 'entry' => $entry->id]) }}">Edit</a>
                                        <form
                                            method="POST"
                                            action="{{ route('tp.content-types.entries.destroy', ['contentType' => $contentType->id, 'entry' => $entry->id]) }}"
                                            data-confirm="Delete this content entry? This cannot be undone.">
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

            <div class="tp-metabox__body">{{ $entries->links() }}</div>
        @endif
    </div>
@endsection
