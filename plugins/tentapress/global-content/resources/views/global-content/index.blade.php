@extends('tentapress-admin::layouts.shell')

@section('title', 'Global Content')

@section('content')
    @php
        $kindFilter = in_array((string) ($kind ?? ''), ['', 'section', 'template_part'], true) ? (string) ($kind ?? '') : '';
        $statusFilter = in_array((string) ($status ?? ''), ['', 'draft', 'published'], true) ? (string) ($status ?? '') : '';
    @endphp

    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Global Content</h1>
            <p class="tp-description">Manage reusable synced sections and template parts.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('tp.global-content.create') }}" class="tp-button-primary">Create global content</a>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__title">
            <form method="GET" action="{{ route('tp.global-content.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('tp.global-content.index', ['q' => $search !== '' ? $search : null]) }}" class="{{ $kindFilter === '' && $statusFilter === '' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        All
                    </a>
                    <a href="{{ route('tp.global-content.index', ['kind' => 'section', 'status' => $statusFilter !== '' ? $statusFilter : null, 'q' => $search !== '' ? $search : null]) }}" class="{{ $kindFilter === 'section' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        Sections
                    </a>
                    <a href="{{ route('tp.global-content.index', ['kind' => 'template_part', 'status' => $statusFilter !== '' ? $statusFilter : null, 'q' => $search !== '' ? $search : null]) }}" class="{{ $kindFilter === 'template_part' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        Template parts
                    </a>
                </div>

                <div class="flex-1"></div>

                <div class="flex flex-wrap gap-2">
                    <select name="status" class="tp-select w-full sm:w-44">
                        <option value="">All statuses</option>
                        <option value="published" @selected($statusFilter === 'published')>Published</option>
                        <option value="draft" @selected($statusFilter === 'draft')>Draft</option>
                    </select>
                    <input type="hidden" name="kind" value="{{ $kindFilter }}" />
                    <input type="text" name="q" class="tp-input w-full sm:w-64" placeholder="Search title, slug, or description..." value="{{ $search }}" />
                    <button type="submit" class="tp-button-secondary">Search</button>
                    @if ($search !== '' || $kindFilter !== '' || $statusFilter !== '')
                        <a href="{{ route('tp.global-content.index') }}" class="tp-button-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        @if ($contents->count() === 0)
            <div class="tp-metabox__body tp-muted text-sm">No global content entries found for the current filters.</div>
        @else
            <div class="tp-table-wrap">
                <table class="tp-table tp-table--responsive tp-table--sticky-head">
                    <thead class="tp-table__thead">
                        <tr>
                            <th class="tp-table__th">Title</th>
                            <th class="tp-table__th">Kind</th>
                            <th class="tp-table__th">Status</th>
                            <th class="tp-table__th">Usage</th>
                            <th class="tp-table__th">Updated</th>
                            <th class="tp-table__th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="tp-table__tbody">
                        @foreach ($contents as $content)
                            <tr class="tp-table__row">
                                <td data-label="Title" class="tp-table__td align-middle py-4">
                                    <div class="flex items-center gap-4 whitespace-nowrap">
                                        <a class="tp-button-link" href="{{ route('tp.global-content.edit', ['globalContent' => $content->id]) }}">
                                            {{ $content->title }}
                                        </a>
                                        <span class="tp-code">{{ $content->slug }}</span>
                                    </div>
                                </td>
                                <td data-label="Kind" class="tp-table__td align-middle py-4">
                                    <span class="tp-badge tp-badge-info">{{ $content->kind === 'template_part' ? 'Template Part' : 'Section' }}</span>
                                </td>
                                <td data-label="Status" class="tp-table__td align-middle py-4">
                                    @if ($content->status === 'published')
                                        <span class="tp-badge tp-badge-success">Published</span>
                                    @else
                                        <span class="tp-badge tp-badge-info">Draft</span>
                                    @endif
                                </td>
                                <td data-label="Usage" class="tp-table__td align-middle py-4">
                                    @if ((int) $content->usages_count > 0)
                                        <span class="rounded border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs text-emerald-800">
                                            {{ (int) $content->usages_count }} reference{{ (int) $content->usages_count === 1 ? '' : 's' }}
                                        </span>
                                    @else
                                        <span class="tp-muted text-xs">Not referenced yet</span>
                                    @endif
                                </td>
                                <td data-label="Updated" class="tp-table__td align-middle py-4 tp-muted">
                                    {{ $content->updated_at?->diffForHumans() ?? '—' }}
                                </td>
                                <td data-label="Actions" class="tp-table__td align-middle py-4">
                                    <div class="tp-muted flex justify-end gap-3 text-xs">
                                        <a href="{{ route('tp.global-content.edit', ['globalContent' => $content->id]) }}" class="tp-button-link">Edit</a>
                                        <form method="POST" action="{{ route('tp.global-content.destroy', ['globalContent' => $content->id]) }}" data-confirm="Delete this global content entry? This cannot be undone.">
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

            <div class="tp-metabox__body">{{ $contents->links() }}</div>
        @endif
    </div>
@endsection
