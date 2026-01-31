@extends('tentapress-admin::layouts.shell')

@section('title', 'Pages')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Pages</h1>
            <p class="tp-description">Create and manage pages.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.pages.create') }}" class="tp-button-primary">Add New</a>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__title">
            <form
                method="GET"
                action="{{ route('tp.pages.index') }}"
                class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="flex gap-2">
                    <a
                        href="{{ route('tp.pages.index', ['status' => 'all', 's' => $search]) }}"
                        class="{{ $status === 'all' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        All
                    </a>
                    <a
                        href="{{ route('tp.pages.index', ['status' => 'published', 's' => $search]) }}"
                        class="{{ $status === 'published' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        Published
                    </a>
                    <a
                        href="{{ route('tp.pages.index', ['status' => 'draft', 's' => $search]) }}"
                        class="{{ $status === 'draft' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        Drafts
                    </a>
                </div>

                <div class="flex-1"></div>

                <div class="flex gap-2">
                    <input
                        name="s"
                        value="{{ $search }}"
                        class="tp-input w-full sm:w-64"
                        placeholder="Search pages…" />
                    <button class="tp-button-secondary" type="submit">Search</button>
                </div>
            </form>
        </div>

        @if ($pages->count() === 0)
            <div class="tp-metabox__body tp-muted text-sm">No pages found.</div>
        @else
            <div class="tp-table-wrap">
                <table class="tp-table">
                    <thead class="tp-table__thead">
                        <tr>
                            <th class="tp-table__th">Title</th>
                            <th class="tp-table__th">Slug</th>
                            <th class="tp-table__th">Status</th>
                            <th class="tp-table__th">Updated</th>
                            <th class="tp-table__th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="tp-table__tbody">
                        @foreach ($pages as $page)
                            <tr class="tp-table__row">
                                <td class="tp-table__td">
                                    <a
                                        class="tp-button-link"
                                        href="{{ route('tp.pages.edit', ['page' => $page->id]) }}">
                                        {{ $page->title }}
                                    </a>
                                </td>
                                <td class="tp-table__td tp-code">/{{ $page->slug }}</td>
                                <td class="tp-table__td">
                                    @if ($page->status === 'published')
                                        <span class="tp-notice-success mb-0 inline-block px-2 py-1 text-xs">
                                            Published
                                        </span>
                                    @else
                                        <span class="tp-notice-info mb-0 inline-block px-2 py-1 text-xs">Draft</span>
                                    @endif
                                </td>
                                <td class="tp-table__td tp-muted">
                                    {{ $page->updated_at?->diffForHumans() ?? '—' }}
                                </td>
                                <td class="tp-table__td">
                                    <div class="flex justify-end gap-3 text-xs text-slate-600">
                                        <a
                                            class="tp-button-link hover:text-slate-900"
                                            href="{{ route('tp.pages.edit', ['page' => $page->id]) }}">
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('tp.pages.destroy', ['page' => $page->id]) }}"
                                            onsubmit="return confirm('Delete this page?');">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="tp-button-link text-red-600 hover:text-red-700">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="tp-metabox__body">
                {{ $pages->links() }}
            </div>
        @endif
    </div>
@endsection
