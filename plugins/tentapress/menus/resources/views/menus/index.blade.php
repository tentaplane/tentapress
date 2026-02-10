@extends('tentapress-admin::layouts.shell')

@section('title', 'Menus')

@section('content')
    @php
        $search = (string) ($search ?? '');
        $sort = in_array(($sort ?? 'updated'), ['name', 'slug', 'items', 'updated'], true) ? $sort : 'updated';
        $direction = ($direction ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $totalMenus = is_numeric($totalMenus ?? null) ? (int) $totalMenus : 0;
        $totalItems = is_numeric($totalItems ?? null) ? (int) $totalItems : 0;
        $assignedLocations = is_numeric($assignedLocations ?? null) ? (int) $assignedLocations : 0;
        $totalLocations = is_numeric($totalLocations ?? null) ? (int) $totalLocations : 0;

        $nextDirectionFor = function (string $column) use ($sort, $direction): string {
            if ($sort !== $column) {
                return in_array($column, ['name', 'slug'], true) ? 'asc' : 'desc';
            }

            return $direction === 'asc' ? 'desc' : 'asc';
        };

        $sortUrlFor = function (string $column) use ($search, $nextDirectionFor): string {
            return route('tp.menus.index', [
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
            <h1 class="tp-page-title">Menus</h1>
            <p class="tp-description">Create navigation menus and assign them to locations in your theme.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.menus.create') }}" class="tp-button-primary">Create menu</a>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-3">
        <div class="rounded-xl border border-black/10 bg-white px-4 py-3">
            <div class="tp-muted text-xs uppercase tracking-wide">Menus</div>
            <div class="mt-2 text-xl font-semibold text-[#1d2327]">{{ number_format($totalMenus) }}</div>
        </div>
        <div class="rounded-xl border border-black/10 bg-white px-4 py-3">
            <div class="tp-muted text-xs uppercase tracking-wide">Items</div>
            <div class="mt-2 text-xl font-semibold text-[#1d2327]">{{ number_format($totalItems) }}</div>
        </div>
        <div class="rounded-xl border border-black/10 bg-white px-4 py-3">
            <div class="tp-muted text-xs uppercase tracking-wide">Assigned locations</div>
            <div class="mt-2 text-xl font-semibold text-[#1d2327]">
                {{ number_format($assignedLocations) }}
                <span class="tp-muted text-sm font-medium">/ {{ number_format($totalLocations) }}</span>
            </div>
        </div>
    </div>

    @if (count($locationsWithMenus) > 0)
        <div class="tp-metabox mb-5">
            <div class="tp-metabox__title">Theme locations</div>
            <div class="tp-metabox__body">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($locationsWithMenus as $loc)
                        @php
                            $label = (string) ($loc['label'] ?? $loc['key'] ?? 'Location');
                            $menuName = isset($loc['menu_name']) ? (string) $loc['menu_name'] : '';
                            $itemCount = isset($loc['item_count']) ? (int) $loc['item_count'] : 0;
                            $locationKey = (string) ($loc['key'] ?? '');
                        @endphp

                        <div class="rounded-xl border border-black/10 bg-white p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-sm font-semibold text-[#1d2327]">{{ $label }}</div>
                                @if ($locationKey !== '')
                                    <span class="tp-code text-[11px]">{{ $locationKey }}</span>
                                @endif
                            </div>
                            @if ($menuName !== '')
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-800">
                                        {{ $menuName }}
                                    </span>
                                    <span class="tp-muted text-xs">{{ $itemCount }} items</span>
                                </div>
                            @else
                                <div class="tp-muted mt-2 text-xs">No menu assigned.</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="tp-metabox">
        <div class="tp-metabox__title">
            <form method="GET" action="{{ route('tp.menus.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="tp-muted text-xs">{{ number_format($menus->total()) }} menus</div>
                <div class="flex-1"></div>
                <div class="flex gap-2">
                    <label class="sr-only" for="menus-search">Search menus</label>
                    <input id="menus-search" name="s" value="{{ $search }}" class="tp-input w-full sm:w-64" placeholder="Search menus…" />
                    <input type="hidden" name="sort" value="{{ $sort }}" />
                    <input type="hidden" name="direction" value="{{ $direction }}" />
                    <button class="tp-button-secondary" type="submit">Search</button>
                </div>
            </form>
        </div>

        @if ($menus->count() === 0)
            <div class="tp-metabox__body tp-muted text-sm">No menus yet. Create your first one.</div>
        @else
            <div class="tp-table-wrap">
                <table class="tp-table tp-table--responsive tp-table--sticky-head">
                    <thead class="tp-table__thead">
                        <tr>
                            <th class="tp-table__th">
                                <a class="inline-flex items-center gap-1.5 py-0.5 hover:text-black/90" href="{{ $sortUrlFor('name') }}">
                                    Name
                                    <span class="inline-flex" aria-hidden="true">{!! $sortIconSvgFor('name') !!}</span>
                                </a>
                            </th>
                            <th class="tp-table__th">
                                <a class="inline-flex items-center gap-1.5 py-0.5 hover:text-black/90" href="{{ $sortUrlFor('slug') }}">
                                    Key
                                    <span class="inline-flex" aria-hidden="true">{!! $sortIconSvgFor('slug') !!}</span>
                                </a>
                            </th>
                            <th class="tp-table__th">Locations</th>
                            <th class="tp-table__th">
                                <a class="inline-flex items-center gap-1.5 py-0.5 hover:text-black/90" href="{{ $sortUrlFor('items') }}">
                                    Items
                                    <span class="inline-flex" aria-hidden="true">{!! $sortIconSvgFor('items') !!}</span>
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
                        @foreach ($menus as $menu)
                            @php
                                $menuId = (int) ($menu->id ?? 0);
                                $locations = $menuLocations[$menuId] ?? [];
                                $itemCount = $counts[$menuId] ?? 0;
                            @endphp

                            <tr class="tp-table__row">
                                <td data-label="Name" class="tp-table__td align-middle py-4">
                                    <a class="tp-button-link" href="{{ route('tp.menus.edit', ['menu' => $menu->id]) }}">
                                        {{ $menu->name }}
                                    </a>
                                </td>
                                <td data-label="Key" class="tp-table__td align-middle py-4">
                                    <span class="tp-code">{{ $menu->slug }}</span>
                                </td>
                                <td data-label="Locations" class="tp-table__td align-middle py-4">
                                    @if ($locations === [])
                                        <span class="tp-muted text-xs">Not assigned</span>
                                    @else
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            @foreach (array_slice($locations, 0, 2) as $locationName)
                                                <span class="rounded-full border border-black/10 bg-black/[0.03] px-2 py-0.5 text-xs text-black/70">
                                                    {{ $locationName }}
                                                </span>
                                            @endforeach
                                            @if (count($locations) > 2)
                                                <span class="tp-muted text-xs">+{{ count($locations) - 2 }} more</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td data-label="Items" class="tp-table__td align-middle py-4">
                                    <span class="rounded-full border border-sky-200 bg-sky-50 px-2 py-0.5 text-xs font-semibold text-sky-800">
                                        {{ $itemCount }}
                                    </span>
                                </td>
                                <td data-label="Updated" class="tp-table__td tp-muted align-middle py-4">{{ $menu->updated_at?->diffForHumans() ?? '—' }}</td>
                                <td data-label="Actions" class="tp-table__td align-middle py-4">
                                    <div class="tp-muted flex justify-end gap-3 text-xs">
                                        <a class="tp-button-link" href="{{ route('tp.menus.edit', ['menu' => $menu->id]) }}">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('tp.menus.destroy', ['menu' => $menu->id]) }}" data-confirm="Delete this menu?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="tp-button-link text-red-600 hover:text-red-700">
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
                {{ $menus->links() }}
            </div>
        @endif
    </div>
@endsection