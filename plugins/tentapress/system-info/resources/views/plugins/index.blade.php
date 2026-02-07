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
                        <tr
                            class="tp-table__row"
                            x-data="tpPluginToggle({
                                id: @js($id),
                                enabled: @js($isEnabled),
                                installed: @js($isInstalled),
                                protectedPlugin: @js($isProtected),
                                token: @js(csrf_token()),
                                enableUrl: @js(route('tp.plugins.enable')),
                                disableUrl: @js(route('tp.plugins.disable')),
                            })">
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
                                <span x-show="enabled" class="tp-notice-success mb-0 inline-block px-2 py-1 text-xs">Enabled</span>
                                <span x-show="!enabled" class="tp-notice-warning mb-0 inline-block px-2 py-1 text-xs">Disabled</span>

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
                                    <button
                                        x-show="enabled"
                                        type="button"
                                        :class="protectedPlugin || loading ? 'tp-button-disabled' : 'tp-button-secondary'"
                                        :disabled="protectedPlugin || loading"
                                        @click="submit('disable')">
                                        <span x-show="!loading">Disable</span>
                                        <span x-show="loading">Working...</span>
                                    </button>
                                    <button
                                        x-show="!enabled"
                                        type="button"
                                        :class="installed && !loading ? 'tp-button-primary' : 'tp-button-disabled'"
                                        :disabled="!installed || loading"
                                        @click="submit('enable')">
                                        <span x-show="!loading">Enable</span>
                                        <span x-show="loading">Working...</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        window.tpPluginToggle = function (config) {
            return {
                id: config.id,
                enabled: Boolean(config.enabled),
                installed: Boolean(config.installed),
                protectedPlugin: Boolean(config.protectedPlugin),
                token: String(config.token || ''),
                enableUrl: String(config.enableUrl || ''),
                disableUrl: String(config.disableUrl || ''),
                loading: false,
                async submit(action) {
                    if (this.loading) {
                        return;
                    }

                    this.loading = true;

                    try {
                        const url = action === 'enable' ? this.enableUrl : this.disableUrl;
                        const body = new URLSearchParams({
                            _token: this.token,
                            id: this.id,
                        });

                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body,
                        });

                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(data.message || 'Unable to update plugin status.');
                        }

                        this.enabled = Boolean(data.enabled);
                        if (window.tpToast) {
                            window.tpToast(data.message || 'Plugin updated.', 'success');
                        }
                    } catch (error) {
                        if (window.tpToast) {
                            window.tpToast(error.message || 'Unable to update plugin status.', 'error');
                        }
                    } finally {
                        this.loading = false;
                    }
                },
            };
        };
    </script>
@endpush
