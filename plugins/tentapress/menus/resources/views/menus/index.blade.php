@extends('tentapress-admin::layouts.shell')

@section('title', 'Menus')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Menus</h1>
            <p class="tp-description">Create navigation menus and assign them to theme locations.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tp.menus.create') }}" class="tp-button-primary">Add Menu</a>
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
                        @endphp

                        <div class="rounded border border-black/10 bg-white p-3">
                            <div class="text-sm font-semibold">{{ $label }}</div>
                            @if ($menuName !== '')
                                <div class="tp-muted mt-1 text-xs">
                                    {{ $menuName }}
                                    <span class="tp-code">({{ $itemCount }} items)</span>
                                </div>
                            @else
                                <div class="tp-muted mt-1 text-xs">No menu assigned.</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="tp-metabox">
        <div class="tp-metabox__title">All menus</div>

        @if ($menus->count() === 0)
            <div class="tp-metabox__body tp-muted text-sm">No menus yet. Create your first menu.</div>
        @else
            <div class="tp-table-wrap">
                <table class="tp-table">
                    <thead class="tp-table__thead">
                        <tr>
                            <th class="tp-table__th">Name</th>
                            <th class="tp-table__th">Slug</th>
                            <th class="tp-table__th">Locations</th>
                            <th class="tp-table__th">Items</th>
                            <th class="tp-table__th">Updated</th>
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
                                <td class="tp-table__td">
                                    <a
                                        class="tp-button-link"
                                        href="{{ route('tp.menus.edit', ['menu' => $menu->id]) }}">
                                        {{ $menu->name }}
                                    </a>
                                </td>
                                <td class="tp-table__td tp-code">{{ $menu->slug }}</td>
                                <td class="tp-table__td tp-muted text-sm">
                                    @if ($locations === [])
                                        —
                                    @else
                                        {{ implode(', ', $locations) }}
                                    @endif
                                </td>
                                <td class="tp-table__td tp-muted">{{ $itemCount }}</td>
                                <td class="tp-table__td tp-muted">{{ $menu->updated_at?->diffForHumans() ?? '—' }}</td>
                                <td class="tp-table__td">
                                    <div class="flex justify-end gap-3 text-xs text-slate-600">
                                        <a
                                            class="tp-button-link hover:text-slate-900"
                                            href="{{ route('tp.menus.edit', ['menu' => $menu->id]) }}">
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('tp.menus.destroy', ['menu' => $menu->id]) }}"
                                            onsubmit="return confirm('Delete this menu?');">
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
                {{ $menus->links() }}
            </div>
        @endif
    </div>
@endsection
