@extends('tentapress-admin::layouts.shell')

@section('title', 'SEO')

@section('content')
    @php
        $sort = in_array(($sort ?? 'updated'), ['title', 'slug', 'updated', 'type', 'custom'], true) ? $sort : 'updated';
        $direction = ($direction ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $type = in_array(($type ?? 'all'), ['all', 'pages', 'posts'], true) ? $type : 'all';
        $seo = in_array(($seo ?? 'all'), ['all', 'custom', 'missing'], true) ? $seo : 'all';
        $search = (string) ($search ?? '');

        $nextDirectionFor = function (string $column) use ($sort, $direction): string {
            if ($sort !== $column) {
                return in_array($column, ['title', 'slug', 'type'], true) ? 'asc' : 'desc';
            }

            return $direction === 'asc' ? 'desc' : 'asc';
        };

        $sortUrlFor = function (string $column) use ($type, $seo, $search, $nextDirectionFor): string {
            return route('tp.seo.index', [
                'type' => $type,
                'seo' => $seo,
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
            <h1 class="tp-page-title">SEO</h1>
            <p class="tp-description">Manage search and social sharing details for pages and posts.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.seo.settings') }}" class="tp-button-secondary">SEO settings</a>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__title">
            <form
                method="GET"
                action="{{ route('tp.seo.index') }}"
                class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="flex flex-wrap gap-2">
                    <a
                        href="{{ route('tp.seo.index', ['type' => 'all', 'seo' => $seo, 's' => $search, 'sort' => $sort, 'direction' => $direction]) }}"
                        class="{{ $type === 'all' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        All
                    </a>
                    <a
                        href="{{ route('tp.seo.index', ['type' => 'pages', 'seo' => $seo, 's' => $search, 'sort' => $sort, 'direction' => $direction]) }}"
                        class="{{ $type === 'pages' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        Pages
                    </a>
                    <a
                        href="{{ route('tp.seo.index', ['type' => 'posts', 'seo' => $seo, 's' => $search, 'sort' => $sort, 'direction' => $direction]) }}"
                        class="{{ $type === 'posts' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        Posts
                    </a>
                </div>

                <div class="flex-1"></div>

                <div class="flex flex-wrap gap-2">
                    <a
                        href="{{ route('tp.seo.index', ['type' => $type, 'seo' => 'all', 's' => $search, 'sort' => $sort, 'direction' => $direction]) }}"
                        class="{{ $seo === 'all' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        All entries
                    </a>
                    <a
                        href="{{ route('tp.seo.index', ['type' => $type, 'seo' => 'custom', 's' => $search, 'sort' => $sort, 'direction' => $direction]) }}"
                        class="{{ $seo === 'custom' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        Custom
                    </a>
                    <a
                        href="{{ route('tp.seo.index', ['type' => $type, 'seo' => 'missing', 's' => $search, 'sort' => $sort, 'direction' => $direction]) }}"
                        class="{{ $seo === 'missing' ? 'tp-button-primary' : 'tp-button-secondary' }}">
                        Missing setup
                    </a>
                </div>

                <div class="flex-1"></div>

                <div class="flex gap-2">
                    <input
                        name="s"
                        value="{{ $search }}"
                        class="tp-input w-full sm:w-64"
                        placeholder="Search titles or slugs…" />
                    <input type="hidden" name="type" value="{{ $type }}" />
                    <input type="hidden" name="seo" value="{{ $seo }}" />
                    <input type="hidden" name="sort" value="{{ $sort }}" />
                    <input type="hidden" name="direction" value="{{ $direction }}" />
                    <button class="tp-button-secondary" type="submit">Search</button>
                </div>
            </form>
        </div>

        @if ($entries->count() === 0)
            <div class="tp-metabox__body tp-muted text-sm">No matching pages or posts found.</div>
        @else
            <div class="tp-table-wrap">
                <table class="tp-table">
                    <thead class="tp-table__thead">
                        <tr>
                            <th class="tp-table__th">
                                <a class="inline-flex items-center gap-1.5 py-0.5 hover:text-black/90" href="{{ $sortUrlFor('type') }}">
                                    Type
                                    <span class="inline-flex" aria-hidden="true">{!! $sortIconSvgFor('type') !!}</span>
                                </a>
                            </th>
                            <th class="tp-table__th w-1/2">
                                <div class="flex items-center gap-4 whitespace-nowrap">
                                    <a class="inline-flex items-center gap-1.5 py-0.5 hover:text-black/90" href="{{ $sortUrlFor('title') }}">
                                        Title
                                        <span class="inline-flex" aria-hidden="true">{!! $sortIconSvgFor('title') !!}</span>
                                    </a>
                                    <a class="inline-flex items-center gap-1 py-0.5 text-black/60 hover:text-black/90" href="{{ $sortUrlFor('slug') }}">
                                        URL
                                        <span class="inline-flex" aria-hidden="true">{!! $sortIconSvgFor('slug') !!}</span>
                                    </a>
                                </div>
                            </th>
                            <th class="tp-table__th">
                                <a class="inline-flex items-center gap-1.5 py-0.5 hover:text-black/90" href="{{ $sortUrlFor('custom') }}">
                                    Custom setup
                                    <span class="inline-flex" aria-hidden="true">{!! $sortIconSvgFor('custom') !!}</span>
                                </a>
                            </th>
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
                        @foreach ($entries as $entry)
                            @php
                                $title = $entry['title'] !== '' ? $entry['title'] : '(Untitled)';
                                $slug = $entry['slug'];
                                $typeLabel = $entry['type'] === 'post' ? 'Post' : 'Page';
                                $isCustom = (bool) $entry['has_seo'];
                                $updatedAt = $entry['updated_at'];
                            @endphp
                            <tr class="tp-table__row">
                                <td class="tp-table__td align-middle py-4">
                                    <span class="tp-code">{{ $typeLabel }}</span>
                                </td>
                                <td class="tp-table__td align-middle py-4">
                                    <div class="flex items-center gap-4 whitespace-nowrap">
                                        <a class="tp-button-link" href="{{ $entry['edit_url'] }}">
                                            {{ $title }}
                                        </a>
                                        <span class="tp-code">/{{ $slug }}</span>
                                    </div>
                                </td>
                                <td class="tp-table__td align-middle py-4">
                                    @if ($isCustom)
                                        <span class="tp-notice-success mb-0 inline-block px-2 py-1 text-xs">Yes</span>
                                    @else
                                        <span class="tp-muted text-xs">No</span>
                                    @endif
                                </td>
                                <td class="tp-table__td tp-muted align-middle py-4">
                                    {{ $updatedAt?->diffForHumans() ?? '—' }}
                                </td>
                                <td class="tp-table__td align-middle py-4">
                                    <div class="flex justify-end gap-3 text-xs text-slate-600">
                                        <a class="tp-button-link hover:text-slate-900" href="{{ $entry['edit_url'] }}">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="tp-metabox__body">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
@endsection
