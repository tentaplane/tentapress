@extends('tentapress-admin::layouts.shell')

@section('title', 'Pages')

@section('content')
    @php
        $sort = in_array(($sort ?? 'updated'), ['title', 'slug', 'status', 'updated'], true) ? $sort : 'updated';
        $direction = ($direction ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $menuUsage = is_array($menuUsage ?? null) ? $menuUsage : [];

        $nextDirectionFor = function (string $column) use ($sort, $direction): string {
            if ($sort !== $column) {
                return in_array($column, ['title', 'slug', 'status'], true) ? 'asc' : 'desc';
            }

            return $direction === 'asc' ? 'desc' : 'asc';
        };

        $sortUrlFor = function (string $column) use ($status, $search, $nextDirectionFor): string {
            return route('tp.pages.index', [
                'status' => $status,
                's' => $search,
                'sort' => $column,
                'direction' => $nextDirectionFor($column),
            ]);
        };

        $sortIconSvgFor = function (string $column) use ($sort, $direction): string {
            if ($sort !== $column) {
                return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 text-black/40"><path fill-rule="evenodd" d="M2.24 6.8a.75.75 0 0 0 1.06-.04l1.95-2.1v8.59a.75.75 0 0 0 1.5 0V4.66l1.95 2.1a.75.75 0 1 0 1.1-1.02l-3.25-3.5a.75.75 0 0 0-1.1 0L2.2 5.74a.75.75 0 0 0 .04 1.06Zm8 6.4a.75.75 0 0 0-.04 1.06l3.25 3.5a.75.75 0 0 0 1.1 0l3.25-3.5a.75.75 0 1 0-1.1-1.02l-1.95 2.1V6.75a.75.75 0 0 0-1.5 0v8.59l-1.95-2.1a.75.75 0 0 0-1.06-.04Z" clip-rule="evenodd" /></svg>';
            }

            $activeClass = 'size-4 text-[#2271b1]';

            if ($direction === 'asc') {
                return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="'.$activeClass.' rotate-180"><path fill-rule="evenodd" d="M10 3a.75.75 0 0 1 .75.75v10.638l3.96-4.158a.75.75 0 1 1 1.08 1.04l-5.25 5.5a.75.75 0 0 1-1.08 0l-5.25-5.5a.75.75 0 1 1 1.08-1.04l3.96 4.158V3.75A.75.75 0 0 1 10 3Z" clip-rule="evenodd" /></svg>';
            }

            return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="'.$activeClass.'"><path fill-rule="evenodd" d="M10 3a.75.75 0 0 1 .75.75v10.638l3.96-4.158a.75.75 0 1 1 1.08 1.04l-5.25 5.5a.75.75 0 0 1-1.08 0l-5.25-5.5a.75.75 0 1 1 1.08-1.04l3.96 4.158V3.75A.75.75 0 0 1 10 3Z" clip-rule="evenodd" /></svg>';
        };
    @endphp

    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Pages</h1>
            <p class="tp-description">Create and manage pages with sorting and menu visibility.</p>
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
                        href="{{ route('tp.pages.index', ['status' => 'all', 's' => $search, 'sort' => $sort, 'direction' => $direction]) }}"
                        class="{{ $status === 'all' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        All
                    </a>
                    <a
                        href="{{ route('tp.pages.index', ['status' => 'published', 's' => $search, 'sort' => $sort, 'direction' => $direction]) }}"
                        class="{{ $status === 'published' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        Published
                    </a>
                    <a
                        href="{{ route('tp.pages.index', ['status' => 'draft', 's' => $search, 'sort' => $sort, 'direction' => $direction]) }}"
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
                    <input type="hidden" name="sort" value="{{ $sort }}" />
                    <input type="hidden" name="direction" value="{{ $direction }}" />
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
                            <th class="tp-table__th w-1/2">
                                <div class="flex items-center gap-4 whitespace-nowrap">
                                    <a class="inline-flex items-center gap-1.5 py-0.5 hover:text-black/90" href="{{ $sortUrlFor('title') }}">
                                        Title
                                        <span class="inline-flex" aria-hidden="true">{!! $sortIconSvgFor('title') !!}</span>
                                    </a>
                                    <a class="inline-flex items-center gap-1 py-0.5 text-black/60 hover:text-black/90" href="{{ $sortUrlFor('slug') }}">
                                        Slug
                                        <span class="inline-flex" aria-hidden="true">{!! $sortIconSvgFor('slug') !!}</span>
                                    </a>
                                </div>
                            </th>
                            <th class="tp-table__th">
                                <a class="inline-flex items-center gap-1.5 py-0.5 hover:text-black/90" href="{{ $sortUrlFor('status') }}">
                                    Status
                                    <span class="inline-flex" aria-hidden="true">{!! $sortIconSvgFor('status') !!}</span>
                                </a>
                            </th>
                            <th class="tp-table__th">Menus</th>
                            <th class="tp-table__th">
                                <a class="inline-flex items-center gap-1.5 py-0.5 hover:text-black/90" href="{{ $sortUrlFor('updated') }}">
                                    Updated
                                    <span class="inline-flex" aria-hidden="true">{!! $sortIconSvgFor('updated') !!}</span>
                                </a>
                            </th>
                            <th class="tp-table__th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="tp-table__tbody">
                        @foreach ($pages as $page)
                            @php
                                $usage = $menuUsage[$page->id] ?? ['count' => 0, 'menus' => []];
                                $linkedCount = isset($usage['count']) ? (int) $usage['count'] : 0;
                                $linkedMenus = isset($usage['menus']) && is_array($usage['menus']) ? $usage['menus'] : [];
                                $primaryMenus = array_slice($linkedMenus, 0, 2);
                                $remainingMenuCount = max($linkedCount - count($primaryMenus), 0);
                            @endphp
                            <tr class="tp-table__row">
                                <td class="tp-table__td align-middle py-4">
                                    <div class="flex items-center gap-4 whitespace-nowrap">
                                        <a
                                            class="tp-button-link"
                                            href="{{ route('tp.pages.edit', ['page' => $page->id]) }}">
                                            {{ $page->title }}
                                        </a>
                                        <span class="tp-code">/{{ $page->slug }}</span>
                                    </div>
                                </td>
                                <td class="tp-table__td align-middle py-4">
                                    @if ($page->status === 'published')
                                        <span class="tp-notice-success mb-0 inline-block px-2 py-1 text-xs">
                                            Published
                                        </span>
                                    @else
                                        <span class="tp-notice-info mb-0 inline-block px-2 py-1 text-xs">Draft</span>
                                    @endif
                                </td>
                                <td class="tp-table__td align-middle py-4">
                                    @if ($linkedCount > 0)
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            @foreach ($primaryMenus as $menuName)
                                                <span class="rounded border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs text-emerald-800">
                                                    {{ $menuName }}
                                                </span>
                                            @endforeach
                                            @if ($remainingMenuCount > 0)
                                                <span class="tp-muted text-xs">+{{ $remainingMenuCount }} more</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="tp-muted text-xs">Not in menus</span>
                                    @endif
                                </td>
                                <td class="tp-table__td tp-muted align-middle py-4">
                                    {{ $page->updated_at?->diffForHumans() ?? '—' }}
                                </td>
                                <td class="tp-table__td align-middle py-4">
                                    <div class="flex justify-end gap-3 text-xs text-slate-600">
                                        <a
                                            class="tp-button-link hover:text-slate-900"
                                            href="{{ route('tp.pages.edit', ['page' => $page->id]) }}">
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('tp.pages.destroy', ['page' => $page->id]) }}"
                                            data-confirm="Delete this page?">
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
