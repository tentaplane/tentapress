@extends('tentapress-admin::layouts.shell')

@section('title', 'Plugins')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Plugins</h1>
            <p class="tp-description">Manage which plugins are available and active on this site.</p>
        </div>

        <div class="flex gap-2">
            <form method="POST" action="{{ route('tp.plugins.sync') }}">
                @csrf
                <button type="submit" class="tp-button-secondary">Refresh plugin list</button>
            </form>
        </div>
    </div>

    @if (! empty($error))
        <div class="tp-notice-error">{{ $error }}</div>
    @endif

    @if (empty($plugins))
        <div class="tp-panel">
            <div class="font-semibold">No plugins found</div>
            <div class="tp-muted mt-1 text-sm">Refresh the list to discover plugins in your project.</div>
        </div>
    @else
        <div class="tp-table-wrap">
            <table class="tp-table">
                <thead class="tp-table__thead">
                    <tr>
                        <th class="tp-table__th">Plugin</th>
                        <th class="tp-table__th">Status</th>
                        <th class="tp-table__th">Version</th>
                        <th class="tp-table__th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="tp-table__tbody">
                    @foreach ($plugins as $plugin)
                        @php
                            $isEnabled = ! empty($plugin['enabled']);
                            $isInstalled = ! empty($plugin['installed']);
                            $isProtected = ! empty($plugin['protected']);
                            $id = (string) ($plugin['id'] ?? '');
                        @endphp
                        <tr class="tp-table__row">
                            <td class="tp-table__td">
                                <div class="font-semibold">{{ $plugin['name'] }}</div>
                                <div class="tp-muted text-xs">{{ $id }}</div>
                                @if (! empty($plugin['description']))
                                    <div class="tp-muted mt-1 text-xs">{{ $plugin['description'] }}</div>
                                @endif
                                @if (! empty($plugin['provider']))
                                    <div class="tp-code mt-1 text-[11px]">Service class: {{ $plugin['provider'] }}</div>
                                @endif
                                @if (! empty($plugin['path']))
                                    <div class="tp-code mt-1 text-[11px]">Path: {{ $plugin['path'] }}</div>
                                @endif
                            </td>
                            <td class="tp-table__td">
                                @if ($isEnabled)
                                    <span class="tp-notice-success mb-0 inline-block px-2 py-1 text-xs">Enabled</span>
                                @else
                                    <span class="tp-notice-warning mb-0 inline-block px-2 py-1 text-xs">Disabled</span>
                                @endif

                                @if (! $isInstalled)
                                    <div class="tp-muted mt-2 text-xs">Not installed. Add it to Composer first.</div>
                                @endif

                                @if ($isProtected)
                                    <div class="tp-muted mt-2 text-xs">Required plugin</div>
                                @endif
                            </td>
                            <td class="tp-table__td tp-code">
                                {{ $plugin['version'] !== '' ? $plugin['version'] : '—' }}
                            </td>
                            <td class="tp-table__td text-right">
                                <div class="flex justify-end gap-2">
                                    @if ($isEnabled)
                                        <form method="POST" action="{{ route('tp.plugins.disable') }}">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $id }}" />
                                            <button
                                                type="submit"
                                                class="{{ $isProtected ? 'tp-button-disabled' : 'tp-button-secondary' }}"
                                                {{ $isProtected ? 'disabled' : '' }}>
                                                Disable
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('tp.plugins.enable') }}">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $id }}" />
                                            <button
                                                type="submit"
                                                class="{{ $isInstalled ? 'tp-button-primary' : 'tp-button-disabled' }}"
                                                {{ $isInstalled ? '' : 'disabled' }}>
                                                Enable
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
@endsection
