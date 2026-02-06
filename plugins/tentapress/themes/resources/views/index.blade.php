@extends('tentapress-admin::layouts.shell')

@section('title', 'Themes')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Themes</h1>
            <p class="tp-description">Choose how your site looks. You can have one active theme at a time.</p>
        </div>

        <div class="flex gap-2">
            <form
                method="POST"
                action="{{ route('tp.themes.sync') }}"
                x-data="{ submitting: false }"
                @submit="submitting = true">
                @csrf
                <button type="submit" class="tp-button-secondary" :disabled="submitting">
                    <span x-show="!submitting">Refresh theme list</span>
                    <span x-show="submitting" x-cloak>Refreshing...</span>
                </button>
            </form>
        </div>
    </div>

    <div class="tp-metabox">
        <div class="tp-metabox__title">
            Installed themes
            <span class="tp-muted font-normal">({{ count($themes) }})</span>
        </div>

        @if (count($themes) === 0)
            <div class="tp-metabox__body tp-muted text-sm">
                No themes found. Add a theme in
                <code class="tp-code">themes/vendor/name</code>.
            </div>
        @else
            <div class="tp-table-wrap">
                <table class="tp-table">
                    <thead class="tp-table__thead">
                        <tr>
                            <th class="tp-table__th w-56">Preview</th>
                            <th class="tp-table__th">Theme</th>
                            <th class="tp-table__th">Version</th>
                            <th class="tp-table__th">Status</th>
                            <th class="tp-table__th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="tp-table__tbody">
                        @foreach ($themes as $theme)
                            @php
                                $id = (string) ($theme['id'] ?? '');
                                $isActive = ($activeId !== null && $activeId === $id);
                            @endphp

                            <tr class="tp-table__row">
                                <td class="tp-table__td">
                                    @php
                                        $id = (string) ($theme['id'] ?? '');
                                        $hasScreenshot = ! empty($theme['has_screenshot']);
                                    @endphp

                                    @if ($hasScreenshot)
                                        <img
                                            src="{{ route('tp.themes.screenshot', ['themePath' => $id]) }}"
                                            alt=""
                                            class="w-48 rounded border border-black/10 bg-white object-cover"
                                            loading="lazy" />
                                    @else
                                        <div
                                            class="tp-muted flex h-16 w-24 items-center justify-center rounded border border-dashed border-black/20 bg-white text-xs">
                                            No preview
                                        </div>
                                    @endif
                                </td>
                                <td class="tp-table__td">
                                    <div class="font-semibold">
                                        <a
                                            class="tp-button-link"
                                            href="{{ route('tp.themes.show', ['themePath' => $id]) }}">
                                            {{ $theme['name'] ?? $id }}
                                        </a>
                                    </div>

                                    <div class="tp-muted mt-1 text-xs">
                                        <code class="tp-code">{{ $id }}</code>
                                        <span class="mx-1">·</span>
                                        <span>{{ $theme['path'] ?? '' }}</span>
                                    </div>

                                    @php
                                        $layouts = is_array($theme['layouts'] ?? null) ? $theme['layouts'] : [];
                                    @endphp

                                    @if (count($layouts) > 0)
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach ($layouts as $layout)
                                                @php
                                                    $key = isset($layout['key']) ? (string) $layout['key'] : '';
                                                    $label = isset($layout['label']) ? (string) $layout['label'] : $key;
                                                @endphp

                                                @if ($key !== '')
                                                    <span
                                                        class="inline-flex items-center rounded border border-black/10 bg-[#f6f7f7] px-2 py-1 text-xs text-black/70">
                                                        {{ $label }}
                                                        <span class="tp-code ml-1 text-[10px] text-black/50">
                                                            {{ $key }}
                                                        </span>
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="tp-muted mt-2 text-xs">No layouts listed.</div>
                                    @endif
                                </td>
                                <td class="tp-table__td tp-code">{{ $theme['version'] ?? '—' }}</td>
                                <td class="tp-table__td">
                                    @if ($isActive)
                                        <span class="tp-notice-success mb-0 inline-block px-2 py-1 text-xs">
                                            Active
                                        </span>
                                    @else
                                        <span class="tp-notice-info mb-0 inline-block px-2 py-1 text-xs">Inactive</span>
                                    @endif
                                </td>
                                <td class="tp-table__td">
                                    <div class="flex justify-end gap-3 text-xs text-slate-600">
                                        <a
                                            class="tp-button-link hover:text-slate-900"
                                            href="{{ route('tp.themes.show', ['themePath' => $id]) }}">
                                            Details
                                        </a>

                                        @if (!$isActive)
                                            <form
                                                method="POST"
                                                action="{{ route('tp.themes.activate') }}"
                                                x-data="{ submitting: false }"
                                                @submit="submitting = true">
                                                @csrf
                                                <input type="hidden" name="theme_id" value="{{ $id }}" />
                                                <button
                                                    type="submit"
                                                    class="tp-button-link text-green-600 hover:text-green-700"
                                                    :disabled="submitting">
                                                    <span x-show="!submitting">Activate</span>
                                                    <span x-show="submitting" x-cloak>Activating…</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
