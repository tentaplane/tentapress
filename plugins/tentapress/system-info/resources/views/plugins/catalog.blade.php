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
        <div class="tp-table-wrap">
            <table class="tp-table tp-table--responsive tp-table--sticky-head">
                <thead class="tp-table__thead">
                    <tr>
                        <th class="tp-table__th">Plugin</th>
                        <th class="tp-table__th">Status</th>
                        <th class="tp-table__th">Installed</th>
                        <th class="tp-table__th">Latest</th>
                        <th class="tp-table__th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="tp-table__tbody">
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
                        @endphp
                        <tr class="tp-table__row">
                            <td data-label="Plugin" class="tp-table__td">
                                <div class="font-semibold">{{ $name }}</div>
                                <div class="tp-muted text-xs">{{ $id }}</div>
                                @if ($description !== '')
                                    <div class="tp-muted mt-1 text-xs">{{ $description }}</div>
                                @endif
                                @if ($docsUrl || $repoUrl)
                                    <div class="mt-2 flex flex-wrap gap-3 text-xs">
                                        @if ($docsUrl)
                                            <a href="{{ $docsUrl }}" target="_blank" rel="noopener" class="text-[var(--tp-brand-700)] hover:underline">Documentation</a>
                                        @endif
                                        @if ($repoUrl)
                                            <a href="{{ $repoUrl }}" target="_blank" rel="noopener" class="text-[var(--tp-brand-700)] hover:underline">Repository</a>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td data-label="Status" class="tp-table__td">
                                @if ($enabled)
                                    <span class="tp-badge tp-badge-success">Enabled</span>
                                @elseif($installed)
                                    <span class="tp-badge tp-badge-warning">Installed</span>
                                @else
                                    <span class="tp-badge tp-badge-neutral">Not installed</span>
                                @endif

                                @if ($localOnly)
                                    <div class="mt-2">
                                        <span class="tp-badge tp-badge-neutral">Local only</span>
                                    </div>
                                @endif
                            </td>
                            <td data-label="Installed" class="tp-table__td tp-code">{{ $installedVersion !== '' ? $installedVersion : '—' }}</td>
                            <td data-label="Latest" class="tp-table__td tp-code">{{ $latestVersion !== '' ? $latestVersion : '—' }}</td>
                            <td data-label="Actions" class="tp-table__td text-right">
                                @if (! $installed)
                                    @if (! $canManagePlugins)
                                        <span class="tp-muted text-xs">You do not have permission to manage plugin installs.</span>
                                    @elseif (! $canInstallPlugins)
                                        <span class="tp-muted text-xs">Only super administrators can queue installs.</span>
                                    @else
                                        <form method="POST" action="{{ route('tp.plugins.install') }}" class="inline-flex">
                                            @csrf
                                            <input type="hidden" name="package" value="{{ $package }}" />
                                            <button type="submit" class="tp-button-primary">Install</button>
                                        </form>
                                    @endif
                                @else
                                    <span class="tp-muted text-xs">Manage status in Plugins</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
