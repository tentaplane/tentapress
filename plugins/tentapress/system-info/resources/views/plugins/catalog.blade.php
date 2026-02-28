@extends('tentapress-admin::layouts.shell')

@section('title', 'Plugin Catalogue')

@section('content')
    <div class="tp-page-header">
        <div>
            <h1 class="tp-page-title">Plugin Catalogue</h1>
            <p class="tp-description">Discover first-party plugins available to your TentaPress installation.</p>
        </div>

        @if ($canManagePlugins)
            <div class="flex gap-2">
                <a href="{{ route('tp.plugins.index') }}" class="tp-button-secondary">Manage installed plugins</a>
            </div>
        @endif
    </div>

    @if (! empty($warning))
        <div class="tp-notice-warning mb-4">{{ $warning }}</div>
    @endif

    @if (empty($entries))
        <div class="tp-panel">
            <div class="font-semibold">No plugins available</div>
            <div class="tp-muted mt-1 text-sm">No first-party plugins were found in the hosted catalog or local discovery.</div>
        </div>
    @else
        <div
            x-data="tpPluginCatalog({
                token: @js($csrfToken),
                installUrl: @js($installUrl),
                statusUrlTemplate: @js($statusUrlTemplate),
                canInstallPlugins: @js($canInstallPlugins),
            })"
            x-init="init()"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($entries as $entry)
                @php
                    $id = (string) ($entry['id'] ?? '');
                    $name = (string) ($entry['name'] ?? $id);
                    $description = (string) ($entry['description'] ?? '');
                    $installed = ! empty($entry['installed']);
                    $enabled = ! empty($entry['enabled']);
                    $localOnly = ! empty($entry['local_only']);
                    $installedVersion = (string) ($entry['installed_version'] ?? '');
                    $latestVersion = (string) ($entry['latest_version'] ?? '');
                    $package = (string) ($entry['package'] ?? $id);
                    $docsUrl = is_string($entry['docs_url'] ?? null) ? (string) $entry['docs_url'] : null;
                    $repoUrl = is_string($entry['repo_url'] ?? null) ? (string) $entry['repo_url'] : null;
                    $icon = is_string($entry['icon'] ?? null) ? trim((string) $entry['icon']) : '';
                    $tags = is_array($entry['tags'] ?? null) ? $entry['tags'] : [];
                @endphp
                <article class="tp-panel overflow-hidden border border-slate-200">
                    <div class="space-y-3 p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-lg font-semibold text-slate-700">
                                {{ $icon !== '' ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($icon, 0, 1)) : \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <h2 class="truncate text-base font-semibold text-slate-900">{{ $name }}</h2>
                                <p class="tp-muted text-xs">{{ $id }}</p>
                            </div>
                        </div>

                        @if ($description !== '')
                            <p class="tp-muted line-clamp-3 text-sm">{{ $description }}</p>
                        @endif

                        @if ($tags !== [])
                            <div class="flex flex-wrap gap-1">
                                @foreach ($tags as $tag)
                                    <span class="tp-badge tp-badge-neutral">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex flex-wrap gap-2">
                            @if ($enabled)
                                <span class="tp-badge tp-badge-success">Enabled</span>
                            @elseif ($installed)
                                <span class="tp-badge tp-badge-warning">Installed</span>
                            @else
                                <span class="tp-badge tp-badge-neutral">Not installed</span>
                            @endif

                            @if ($localOnly)
                                <span class="tp-badge tp-badge-neutral">Local only</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 rounded-md bg-slate-50 p-2 text-xs">
                            <div>
                                <div class="tp-muted uppercase">Installed</div>
                                <div class="tp-code mt-1">{{ $installedVersion !== '' ? $installedVersion : '—' }}</div>
                            </div>
                            <div>
                                <div class="tp-muted uppercase">Latest</div>
                                <div class="tp-code mt-1">{{ $latestVersion !== '' ? $latestVersion : '—' }}</div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 text-xs">
                            @if ($docsUrl)
                                <a href="{{ $docsUrl }}" target="_blank" rel="noopener" class="text-[var(--tp-brand-700)] hover:underline">Documentation</a>
                            @endif
                            @if ($repoUrl)
                                <a href="{{ $repoUrl }}" target="_blank" rel="noopener" class="text-[var(--tp-brand-700)] hover:underline">Repository</a>
                            @endif
                        </div>

                        <div x-data="tpCatalogInstallCard({ id: @js($id), package: @js($package), installed: @js($installed) })" class="space-y-2">
                            @if (! $installed)
                                @if (! $canManagePlugins)
                                    <p class="tp-muted text-xs">You do not have permission to manage plugin installs.</p>
                                @elseif (! $canInstallPlugins)
                                    <p class="tp-muted text-xs">Only super administrators can queue installs.</p>
                                @else
                                    <button
                                        type="button"
                                        class="tp-button-primary w-full"
                                        :class="(loading || isComplete()) ? 'tp-button-disabled' : 'tp-button-primary'"
                                        :disabled="loading || isComplete()"
                                        @click="submitInstall()">
                                        <span x-show="!loading && !status">Install</span>
                                        <span x-show="loading">Queueing...</span>
                                        <span x-show="!loading && status === 'pending'">Queued</span>
                                        <span x-show="!loading && status === 'running'">Installing...</span>
                                        <span x-show="!loading && status === 'success'">Installed</span>
                                        <span x-show="!loading && status === 'failed'">Retry install</span>
                                    </button>
                                    <template x-if="message">
                                        <p class="tp-muted text-xs" x-text="message"></p>
                                    </template>
                                    <template x-if="status === 'failed' && manualCommand">
                                        <p class="tp-code text-[11px]" x-text="manualCommand"></p>
                                    </template>
                                @endif
                            @else
                                <p class="tp-muted text-xs">Manage status in Plugins.</p>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        window.tpPluginCatalog = function (config) {
            return {
                token: String(config.token || ''),
                installUrl: String(config.installUrl || ''),
                statusUrlTemplate: String(config.statusUrlTemplate || ''),
                canInstallPlugins: Boolean(config.canInstallPlugins),
                pollTimer: null,
                attempts: {},
                init() {
                    window.__tpPluginCatalog = this;
                },
                registerAttempt(pluginId, attempt) {
                    if (!attempt || !attempt.id) {
                        return;
                    }

                    this.attempts[pluginId] = attempt;
                    this.startPolling();
                },
                getAttempt(pluginId) {
                    return this.attempts[pluginId] || null;
                },
                startPolling() {
                    if (this.pollTimer) {
                        return;
                    }

                    this.pollTimer = window.setInterval(() => {
                        this.poll();
                    }, 2500);
                },
                stopPolling() {
                    if (!this.pollTimer) {
                        return;
                    }

                    window.clearInterval(this.pollTimer);
                    this.pollTimer = null;
                },
                async poll() {
                    const active = Object.entries(this.attempts).filter(([, attempt]) =>
                        attempt && (attempt.status === 'pending' || attempt.status === 'running')
                    );

                    if (active.length === 0) {
                        this.stopPolling();
                        return;
                    }

                    await Promise.all(active.map(async ([pluginId, attempt]) => {
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

                            this.attempts[pluginId] = data.attempt;
                        } catch {
                        }
                    }));
                },
            };
        };

        window.tpCatalogInstallCard = function (config) {
            return {
                id: String(config.id || ''),
                package: String(config.package || ''),
                installed: Boolean(config.installed),
                loading: false,
                status: null,
                message: '',
                manualCommand: '',
                get root() {
                    return window.__tpPluginCatalog || null;
                },
                syncFromAttempt() {
                    if (!this.root) {
                        return;
                    }

                    const attempt = this.root.getAttempt(this.id);
                    if (!attempt) {
                        return;
                    }

                    this.status = attempt.status || null;
                    this.manualCommand = attempt.manual_command || '';

                    if (this.status === 'success') {
                        this.message = 'Install completed. Refresh plugin list to enable it.';
                    } else if (this.status === 'failed') {
                        this.message = attempt.error || 'Install failed. Review the manual command.';
                    } else if (this.status === 'running') {
                        this.message = 'Install is running...';
                    } else if (this.status === 'pending') {
                        this.message = 'Install queued...';
                    }
                },
                isComplete() {
                    return this.status === 'success';
                },
                async submitInstall() {
                    if (this.loading || this.installed || !this.root || !this.root.canInstallPlugins) {
                        return;
                    }

                    this.loading = true;
                    this.message = '';
                    this.manualCommand = '';

                    try {
                        const body = new URLSearchParams({
                            _token: this.root.token,
                            package: this.package,
                        });

                        const response = await fetch(this.root.installUrl, {
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

                        this.status = 'pending';
                        this.message = data.message || 'Install queued.';

                        if (data.attempt && typeof data.attempt === 'object') {
                            this.root.registerAttempt(this.id, data.attempt);
                        }

                        if (window.tpToast) {
                            window.tpToast(this.message, 'success');
                        }
                    } catch (error) {
                        this.status = 'failed';
                        this.message = error.message || 'Unable to queue plugin install.';
                        if (window.tpToast) {
                            window.tpToast(this.message, 'error');
                        }
                    } finally {
                        this.loading = false;
                    }
                },
                init() {
                    window.setInterval(() => {
                        this.syncFromAttempt();
                    }, 1500);
                },
            };
        };
    </script>
@endpush
