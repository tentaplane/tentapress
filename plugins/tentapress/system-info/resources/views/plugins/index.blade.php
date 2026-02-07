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

    <div
        class="tp-panel mb-4"
        x-data="tpPluginInstaller({
            token: @js(csrf_token()),
            canInstallPlugins: @js($canInstallPlugins),
            installTableExists: @js($installTableExists),
            installUrl: @js(route('tp.plugins.install')),
            statusUrlTemplate: @js(route('tp.plugins.install-attempts.show', ['installId' => '__ID__'])),
            initialAttempts: @js($installAttempts),
        })">
        <div class="flex flex-col gap-3">
            <div>
                <h2 class="text-base font-semibold">Install plugin</h2>
                <p class="tp-muted mt-1 text-sm">
                    Enter <span class="tp-code">vendor/package</span> or
                    <span class="tp-code">https://github.com/vendor/package</span>.
                </p>
            </div>

            <template x-if="!installTableExists">
                <div class="tp-notice-warning mb-0">Install tracking table is missing. Run migrations first.</div>
            </template>
            <template x-if="installTableExists && !canInstallPlugins">
                <div class="tp-notice-warning mb-0">Only super administrators can queue plugin installs.</div>
            </template>

            <form class="flex flex-col gap-2 sm:flex-row sm:items-center" @submit.prevent="submit">
                <input
                    type="text"
                    x-model="packageName"
                    placeholder="vendor/package or github.com/vendor/package"
                    class="tp-input w-full sm:max-w-sm"
                    :disabled="!canInstallPlugins || !installTableExists || submitting" />
                <button
                    type="submit"
                    class="tp-button-primary w-full sm:w-auto"
                    :class="(!canInstallPlugins || !installTableExists || submitting || !packageName.trim()) ? 'tp-button-disabled' : 'tp-button-primary'"
                    :disabled="!canInstallPlugins || !installTableExists || submitting || !packageName.trim()">
                    <span x-show="!submitting">Install</span>
                    <span x-show="submitting">Queueing...</span>
                </button>
            </form>

            <div class="mt-2">
                <div class="mb-2 text-sm font-semibold">Recent install attempts</div>
                <template x-if="attempts.length === 0">
                    <div class="tp-muted text-sm">No install attempts yet.</div>
                </template>
                <template x-if="attempts.length > 0">
                    <div class="tp-table-wrap">
                        <table class="tp-table">
                            <thead class="tp-table__thead">
                                <tr>
                                    <th class="tp-table__th">Package</th>
                                    <th class="tp-table__th">Status</th>
                                    <th class="tp-table__th">When</th>
                                </tr>
                            </thead>
                            <tbody class="tp-table__tbody">
                                <template x-for="attempt in attempts" :key="attempt.id">
                                    <tr class="tp-table__row">
                                        <td class="tp-table__td">
                                            <div class="tp-code text-xs" x-text="attempt.package"></div>
                                            <template x-if="attempt.error">
                                                <div class="tp-muted mt-1 text-xs" x-text="attempt.error"></div>
                                            </template>
                                        </td>
                                        <td class="tp-table__td">
                                            <span
                                                class="tp-badge"
                                                :class="badgeClass(attempt.status)"
                                                x-text="attempt.status"></span>
                                        </td>
                                        <td class="tp-table__td">
                                            <span class="tp-muted text-xs" x-text="formatDate(attempt.created_at)"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @if (empty($plugins))
        <div class="tp-panel">
            <div class="font-semibold">No plugins found</div>
            <div class="tp-muted mt-1 text-sm">Refresh the list to discover plugins in your project.</div>
        </div>
    @else
        <div class="tp-table-wrap">
            <table class="tp-table tp-table--sticky-head">
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
                                <span x-show="enabled" class="tp-badge tp-badge-success">Enabled</span>
                                <span x-show="!enabled" class="tp-badge tp-badge-warning">Disabled</span>

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

        window.tpPluginInstaller = function (config) {
            return {
                token: String(config.token || ''),
                installUrl: String(config.installUrl || ''),
                statusUrlTemplate: String(config.statusUrlTemplate || ''),
                canInstallPlugins: Boolean(config.canInstallPlugins),
                installTableExists: Boolean(config.installTableExists),
                packageName: '',
                submitting: false,
                pollTimer: null,
                attempts: Array.isArray(config.initialAttempts) ? config.initialAttempts : [],
                init() {
                    this.startPollingIfNeeded();
                },
                badgeClass(status) {
                    if (status === 'success') {
                        return 'tp-badge-success';
                    }

                    if (status === 'failed') {
                        return 'tp-badge-error';
                    }

                    if (status === 'running') {
                        return 'tp-badge-warning';
                    }

                    return 'tp-badge-neutral';
                },
                formatDate(value) {
                    if (!value) {
                        return '—';
                    }

                    const date = new Date(value);
                    if (Number.isNaN(date.getTime())) {
                        return String(value);
                    }

                    return date.toLocaleString();
                },
                hasActiveAttempts() {
                    return this.attempts.some((attempt) => attempt.status === 'pending' || attempt.status === 'running');
                },
                startPollingIfNeeded() {
                    if (!this.hasActiveAttempts()) {
                        this.stopPolling();
                        return;
                    }

                    if (this.pollTimer) {
                        return;
                    }

                    this.pollTimer = window.setInterval(() => {
                        this.pollActiveAttempts();
                    }, 2500);
                },
                stopPolling() {
                    if (!this.pollTimer) {
                        return;
                    }

                    window.clearInterval(this.pollTimer);
                    this.pollTimer = null;
                },
                async pollActiveAttempts() {
                    const active = this.attempts.filter((attempt) => attempt.status === 'pending' || attempt.status === 'running');
                    if (active.length === 0) {
                        this.stopPolling();
                        return;
                    }

                    await Promise.all(
                        active.map(async (attempt) => {
                            try {
                                const url = this.statusUrlTemplate.replace('__ID__', String(attempt.id));
                                const response = await fetch(url, {
                                    method: 'GET',
                                    headers: {
                                        Accept: 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest',
                                    },
                                });

                                if (!response.ok) {
                                    return;
                                }

                                const data = await response.json().catch(() => ({}));
                                if (!data.attempt || typeof data.attempt !== 'object') {
                                    return;
                                }

                                const index = this.attempts.findIndex((item) => item.id === data.attempt.id);
                                if (index >= 0) {
                                    this.attempts[index] = data.attempt;
                                }
                            } catch {
                            }
                        }),
                    );

                    this.startPollingIfNeeded();
                },
                async submit() {
                    if (this.submitting || !this.canInstallPlugins || !this.installTableExists) {
                        return;
                    }

                    const packageName = this.packageName.trim().toLowerCase();
                    if (!packageName) {
                        return;
                    }

                    this.submitting = true;

                    try {
                        const body = new URLSearchParams({
                            _token: this.token,
                            package: packageName,
                        });

                        const response = await fetch(this.installUrl, {
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
                            throw new Error(data.message || 'Unable to queue plugin install.');
                        }

                        if (data.attempt && typeof data.attempt === 'object') {
                            this.attempts = [data.attempt, ...this.attempts.filter((item) => item.id !== data.attempt.id)].slice(0, 12);
                            this.startPollingIfNeeded();
                        }

                        this.packageName = '';
                        if (window.tpToast) {
                            window.tpToast(data.message || 'Install queued.', 'success');
                        }
                    } catch (error) {
                        if (window.tpToast) {
                            window.tpToast(error.message || 'Unable to queue plugin install.', 'error');
                        }
                    } finally {
                        this.submitting = false;
                    }
                },
            };
        };
    </script>
@endpush
